<?php

namespace App\Services;

use App\Models\Plugin;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use ZipArchive;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PluginManagerService
{
    protected string $pluginsDir;

    public function __construct()
    {
        $this->pluginsDir = base_path("plugins");
    }

    /**
     * Scan the plugins directory and sync with the database.
     */
    public function discoverAndSyncPlugins(): void
    {
        if (!File::isDirectory($this->pluginsDir)) {
            File::makeDirectory($this->pluginsDir, 0755, true);
            return; // No plugins to discover if directory was just created
        }

        $discoveredPlugins = [];
        $pluginDirectories = File::directories($this->pluginsDir);

        foreach ($pluginDirectories as $pluginPath) {
            $manifestPath = $pluginPath . '/plugin.json';
            if (File::exists($manifestPath)) {
                try {
                    $manifestContent = File::get($manifestPath); // Get content first
                    $manifest = json_decode($manifestContent, true);

                    // --- ADICIONAR ESTA VERIFICAÇÃO ---
                    if ($manifest === null) {
                        // Handle JSON decoding errors
                        $jsonError = json_last_error_msg();
                        Log::error("Manifest file {$manifestPath} contains invalid JSON: {$jsonError}. Skipping plugin discovery for this directory.");
                        continue; // Skip to the next directory
                    }
                    // --- FIM DA ADIÇÃO ---

                    if ($this->isValidManifest($manifest)) {
                        $discoveredPlugins[$manifest["identifier"]] = [
                            "path" => $pluginPath,
                            "manifest" => $manifest,
                        ];
                    } else {
                        // Log if the JSON is valid but missing required fields
                        Log::warning("Manifest file {$manifestPath} is missing required fields (name, identifier, version, provider). Skipping plugin discovery for this directory.");
                    }
                } catch (\Exception $e) {
                    // This catch will now handle other potential errors, like file reading permissions
                    Log::error("Error reading or processing manifest for plugin in {$pluginPath}: " . $e->getMessage());
                }
            }
        }

        // Sync with DB
        $dbPlugins = Plugin::all()->keyBy("identifier");

        // Add new or update existing plugins
        foreach ($discoveredPlugins as $identifier => $pluginData) {
            $plugin = $dbPlugins->get($identifier);
            $manifestData = $pluginData["manifest"];

            $attributes = [
                "name" => $manifestData["name"],
                "version" => $manifestData["version"],
                "description" => $manifestData["description"] ?? null,
                "author" => $manifestData["author"] ?? null,
                "provider_class" => $manifestData["provider"],
                "path" => str_replace(base_path() . '/', '', $pluginData["path"]),
                "permissions_requested" => $manifestData["permissions_requested"] ?? [],
            ];

            if ($plugin) {
                // Update if version or other manifest data changed
                // Consider also updating description, author, path, permissions if they change in the manifest?
                if ($plugin->version !== $manifestData["version"] || $plugin->provider_class !== $manifestData["provider"] ||
                    $plugin->description !== ($manifestData["description"] ?? null) ||
                    $plugin->author !== ($manifestData["author"] ?? null) ||
                    $plugin->path !== str_replace(base_path() . '/', '', $pluginData["path"]) // Check path in case directory name changed
                    ) {
                    $plugin->update($attributes);
                }
            } else {
                // Create new plugin record (inactive by default)
                Plugin::create(array_merge($attributes, [
                    "identifier" => $identifier,
                    "is_active" => false, // New plugins are inactive by default
                    "installed_at" => now(),
                ]));
            }
        }

        // Mark plugins as removed if their directory is gone
        // For now, we'll just log this. Actual deletion should be a manual admin action.
        foreach ($dbPlugins as $identifier => $dbPlugin) {
            if (!isset($discoveredPlugins[$identifier])) {
                // Optionally, mark as inactive or log as missing
                // $dbPlugin->update(["is_active" => false]); // Or maybe add a 'missing_on_disk' flag?
                Log::info("Plugin '{$identifier}' found in DB but not in filesystem.");
            }
        }
    }

    protected function isValidManifest(array $manifest): bool
    {
        // Ensure all required keys are present
        return isset($manifest["name"], $manifest["identifier"], $manifest["version"], $manifest["provider"]);
    }

    /**
     * Activate a plugin.
     */
    public function activatePlugin(string $identifier): bool
    {
        $plugin = Plugin::where("identifier", $identifier)->first();
        if (!$plugin || $plugin->is_active) {
            return false;
        }

        try {
            // Ensure the plugin files still exist
            $pluginPath = base_path($plugin->path);
            if (!File::isDirectory($pluginPath)) {
                 Log::error("Cannot activate plugin {$identifier}: Filesystem directory not found at {$pluginPath}.");
                 return false;
            }

            // Call activate method on plugin's service provider if exists
            if (class_exists($plugin->provider_class)) {
                $provider = app()->make($plugin->provider_class, ['app' => app()]); // Use app() to resolve dependencies properly
                if (method_exists($provider, "activate")) {
                    // Use app()->call to inject dependencies into the activate method
                    app()->call([$provider, "activate"]);
                }
            } else {
                 Log::error("Cannot activate plugin {$identifier}: Provider class '{$plugin->provider_class}' not found.");
                 // Optionally, mark plugin as errored/inactive in DB
                 // $plugin->update(['is_active' => false, 'status' => 'provider_missing']);
                 return false; // Or maybe log and continue, depending on desired behavior
            }

            $plugin->update(["is_active" => true]);
            $this->clearCache();
            return true;
        } catch (\Exception $e) {
            Log::error("Error activating plugin {$identifier}: " . $e->getMessage());
            // Optionally, mark plugin as inactive on error
            // if ($plugin) { $plugin->update(["is_active" => false]); }
            return false;
        }
    }

    /**
     * Deactivate a plugin.
     */
    public function deactivatePlugin(string $identifier): bool
    {
        $plugin = Plugin::where("identifier", $identifier)->first();
        if (!$plugin || !$plugin->is_active) {
            return false;
        }

        try {
             // Ensure the plugin files still exist
            $pluginPath = base_path($plugin->path);
            // We can still deactivate even if files are gone, useful for cleanup
            // if (!File::isDirectory($pluginPath)) {
            //      Log::warning("Deactivating plugin {$identifier}: Filesystem directory not found at {$pluginPath}.");
            //      // Decide if you return false or continue to update DB
            // }


            // Call deactivate method on plugin's service provider if exists
            // Need to be careful here: if the provider class is missing, this will fail.
            // A robust system might require admin intervention if provider is gone.
            if (class_exists($plugin->provider_class)) {
                $provider = app()->make($plugin->provider_class, ['app' => app()]); // Use app() to resolve dependencies properly
                if (method_exists($provider, "deactivate")) {
                    // Use app()->call to inject dependencies into the deactivate method
                    app()->call([$provider, "deactivate"]);
                }
            } else {
                Log::warning("Could not call deactivate method for plugin {$identifier}: Provider class '{$plugin->provider_class}' not found. Deactivating in DB only.");
                 // Continue to update DB even if provider is missing/failed
            }

            $plugin->update(["is_active" => false]);
            $this->clearCache();
            return true;
        } catch (\Exception $e) {
            Log::error("Error deactivating plugin {$identifier}: " . $e->getMessage());
             // Optionally, mark plugin status as errored
            // if ($plugin) { $plugin->update(["is_active" => false, 'status' => 'deactivation_failed']); }
            return false;
        }
    }

    /**
     * Get all active plugin service providers.
     */
    public function getActivePluginProviders(): array
    {
        try {
            // 1. Tenta obter a conexão PDO para verificar se o DB está realmente acessível
            DB::connection()->getPdo();

            // 2. Verifica se a tabela plugins existe
            // Using Schema::hasTable is generally safer than querying blindly
            if (!Schema::hasTable("plugins")) {
                // This happens during initial setup (e.g., migrate command)
                // Log::info("PluginManagerService: Tabela 'plugins' ainda não existe. Retornando array vazio para getActivePluginProviders."); // Too verbose
                return [];
            }

            // 3. Busca os plugins ativos
            $activePlugins = Plugin::where("is_active", true)->get();

            if ($activePlugins->isEmpty()) {
                // Log::info("PluginManagerService: Nenhum plugin ativo encontrado no banco de dados."); // Too verbose
                return [];
            }

            // Filter out providers that don't exist (e.g., if files were manually deleted)
            $providers = $activePlugins->pluck("provider_class")->filter(function($providerClass) {
                 if (!class_exists($providerClass)) {
                     Log::warning("PluginManagerService: Provider class '{$providerClass}' not found for an active plugin. It might be missing from the filesystem. Skipping this provider.");
                     // Consider marking the plugin as inactive in the DB here?
                     // $plugin = Plugin::where('provider_class', $providerClass)->first();
                     // if ($plugin) $plugin->update(['is_active' => false, 'status' => 'provider_missing']);
                     return false; // Do not include this provider
                 }
                 return true; // Include valid provider
            })->all();

            return $providers;

        } catch (\PDOException $e) {
            // Catch DB connection errors specifically
            // This is common during initial setup before DB is configured/migrated
            // Log::warning("PluginManagerService: Falha na conexão PDO com o banco de dados ao tentar obter providers ativos: " . $e->getMessage()); // Too verbose during setup
             // Return empty array as DB is inaccessible
            return [];
        } catch (\Illuminate\Database\QueryException $e) {
             // Catch Query errors (e.g., table exists but column missing, etc.)
            if (Str::contains($e->getMessage(), ['Base table or view not found', 'relation "plugins" does not exist'])) {
                 // This specifically handles the table not existing, which Schema::hasTable should ideally catch,
                 // but this is a fallback in case Schema::hasTable behaves unexpectedly or query runs before check.
                 // Log::info("PluginManagerService: Tabela 'plugins' ainda não existe (QueryException). Retornando array vazio."); // Too verbose
            } else {
                 // Log other query exceptions
                 Log::error("PluginManagerService: Erro de Query ao tentar obter providers ativos: " . $e->getMessage());
            }
            return [];
        } catch (\Exception $e) {
            // Catch any other unexpected errors
            Log::error("PluginManagerService: Erro inesperado ao obter providers ativos: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Handle plugin upload and installation.
     */
    public function installPluginFromZip(string $zipPath, bool $overwrite = false): array
    {
        if (!File::exists($zipPath) || File::extension($zipPath) !== "zip") {
            return ["success" => false, "message" => "Invalid ZIP file provided."];
        }

        $zip = new ZipArchive;
        if ($zip->open($zipPath) !== TRUE) {
            return ["success" => false, "message" => "Failed to open ZIP file."];
        }

        // Check for manifest.json in the root of the zip
        $manifestContent = $zip->getFromName("plugin.json");
        if ($manifestContent === false) {
            $zip->close();
            return ["success" => false, "message" => "plugin.json not found in the root of the ZIP file."];
        }

        try {
            $manifest = json_decode($manifestContent, true);
            // Check for JSON decode error AND manifest validity
            if ($manifest === null) {
                 $jsonError = json_last_error_msg();
                 $zip->close();
                 return ["success" => false, "message" => "Error parsing plugin.json from ZIP: Invalid JSON. Details: {$jsonError}"];
            }

            if (!$this->isValidManifest($manifest)) {
                $zip->close();
                return ["success" => false, "message" => "Invalid plugin.json content: Missing required fields (name, identifier, version, provider)."];
            }
        } catch (\Exception $e) {
            // Catch any other exceptions during parsing
            $zip->close();
            return ["success" => false, "message" => "Error processing plugin.json from ZIP: " . $e->getMessage()];
        }

        $pluginIdentifier = $manifest["identifier"];
        $targetPluginPath = $this->pluginsDir . '/' . $pluginIdentifier;

        if (File::isDirectory($targetPluginPath)) {
            if (!$overwrite) {
                $zip->close();
                return ["success" => false, "message" => "Plugin '{$pluginIdentifier}' already exists. Choose overwrite to replace."];
            }
            // Deactivate and delete existing before overwrite
            Log::info("Overwriting existing plugin {$pluginIdentifier}. Deactivating and deleting old files.");
            $this->deactivatePlugin($pluginIdentifier); // Attempt deactivation first
            if (!$this->deletePluginFiles($pluginIdentifier)) { // Delete files
                 $zip->close();
                 Log::error("Failed to delete old plugin files for {$pluginIdentifier} during overwrite.");
                 return ["success" => false, "message" => "Failed to remove existing plugin files for '{$pluginIdentifier}'. Overwrite failed."];
            }
             // Delete DB record after successful file deletion, so discoverAndSync creates a new one
             $existingPlugin = Plugin::where("identifier", $pluginIdentifier)->first();
             if($existingPlugin) {
                 $existingPlugin->delete();
             }
             $this->clearCache(); // Clear cache after deletion
        }

        // Ensure target directory is clean/created before extracting
        if (File::isDirectory($targetPluginPath)) {
            // If overwrite logic failed to delete, try again? Or just error? Let's error for safety.
            $zip->close();
            return ["success" => false, "message" => "Target directory for plugin '{$pluginIdentifier}' exists and could not be cleared for extraction."];
        }

        // Create target directory
        if (!File::makeDirectory($targetPluginPath, 0755, true, true)) {
             $zip->close();
             return ["success" => false, "message" => "Failed to create directory for plugin '{$pluginIdentifier}'."];
        }


        // Extract the zip
        if (!$zip->extractTo($targetPluginPath)) {
             $zip->close();
             File::deleteDirectory($targetPluginPath); // Cleanup partially extracted files
             return ["success" => false, "message" => "Failed to extract ZIP content for plugin '{$pluginIdentifier}'."];
        }
        $zip->close();

        // Optional: Delete the original uploaded zip file if it's not needed anymore
        // if (File::exists($zipPath)) { // Check exists before deleting
        //     File::delete($zipPath);
        // }


        // Re-discover and sync to add it to DB (as inactive)
        $this->discoverAndSyncPlugins();

        // Check if it was added
        $newPlugin = Plugin::where("identifier", $pluginIdentifier)->first();
        if ($newPlugin) {
            // Optionally, you might want to run migrate or other setup steps here
            // E.g., Artisan::call('migrate', ['--path' => $newPlugin->path . '/database/migrations']);
            // This needs careful handling within plugins themselves.
            return ["success" => true, "message" => "Plugin '{$pluginIdentifier}' installed successfully. Please activate it from the plugin list.", "identifier" => $pluginIdentifier];
        } else {
            // Cleanup if something went wrong after extraction and sync failed
            Log::error("Plugin '{$pluginIdentifier}' extracted to {$targetPluginPath} but was not found in DB after discovery. Cleaning up files.");
            File::deleteDirectory($targetPluginPath);
            return ["success" => false, "message" => "Plugin '{$pluginIdentifier}' extracted but failed to register in the database. Please check logs."];
        }
    }

    /**
     * Delete plugin files and database record.
     * Optionally removes associated database tables/data - this needs plugin-specific logic.
     */
    public function deletePlugin(string $identifier): bool
    {
        $plugin = Plugin::where("identifier", $identifier)->first();
        if (!$plugin) {
            Log::warning("Attempted to delete non-existent plugin: {$identifier}");
            return false; // Plugin not found
        }

        // Deactivate first if active
        if ($plugin->is_active) {
            Log::info("Deactivating plugin {$identifier} before deletion.");
            if (!$this->deactivatePlugin($identifier)) {
                 // Decide if you want to stop deletion if deactivation fails
                 Log::error("Failed to deactivate plugin {$identifier}. Aborting deletion.");
                 return false;
            }
        }

        // --- IMPORTANT: Implement plugin-specific database cleanup here ---
        // This is the trickiest part. A plugin *should* provide a way to drop its tables,
        // but forcing it from here is complex. You might need a 'uninstall' method
        // in the provider or rely on manual cleanup/migrations rollback if the plugin provides them.
        // For now, we'll just delete the record and files.
        // Example (Conceptual, requires plugin migration paths):
        // try {
        //     if ($plugin->path && File::isDirectory(base_path($plugin->path . '/database/migrations'))) {
        //          Artisan::call('migrate:rollback', ['--path' => $plugin->path . '/database/migrations']);
        //     }
        // } catch (\Exception $e) {
        //      Log::error("Failed to rollback migrations for plugin {$identifier}: " . $e->getMessage());
        //      // Decide if you want to continue with file/record deletion or abort
        //      // For safety, you might abort here.
        //      // return false;
        // }
         // --- End of DB Cleanup Consideration ---


        // Delete database record first to mark it for removal
        // This prevents it from being re-synced by discoverAndSync if file deletion fails.
        $identifierTemp = $plugin->identifier; // Keep identifier for logging
        $plugin->delete();
        Log::info("Database record for plugin {$identifierTemp} deleted.");


        // Then delete files
        if ($this->deletePluginFiles($identifierTemp)) {
            Log::info("Files for plugin {$identifierTemp} deleted successfully.");
            $this->clearCache();
            return true;
        } else {
            // Files deletion failed, but DB record is gone. Log this as a partial failure.
            Log::error("Failed to delete plugin files for {$identifierTemp}. Database record was removed, but files remain.");
            // The DB record is gone, so it won't reappear unless manually added or files are moved/renamed.
            return false; // Indicate partial failure
        }
    }

    /**
     * Helper to delete plugin files.
     */
    protected function deletePluginFiles(string $identifier): bool
    {
        $plugin = Plugin::where('identifier', $identifier)->first();
        // Get path from DB if possible, fallback to default convention
        $pluginPath = $plugin ? base_path($plugin->path) : $this->pluginsDir . '/' . $identifier;

        if (File::isDirectory($pluginPath)) {
             try {
                $result = File::deleteDirectory($pluginPath);
                if (!$result) {
                     Log::error("File::deleteDirectory failed for path: {$pluginPath}");
                }
                return $result;
             } catch (\Exception $e) {
                 Log::error("Exception during File::deleteDirectory for path {$pluginPath}: " . $e->getMessage());
                 return false;
             }

        }
        Log::info("Plugin directory not found, nothing to delete: {$pluginPath}");
        return true; // Path doesn't exist, so considered deleted
    }

    /**
     * Clear relevant caches after plugin status changes.
     */
    protected function clearCache(): void
    {
        Log::info("Clearing application caches.");
        try { Artisan::call("cache:clear"); } catch (\Exception $e) { Log::warning("cache:clear failed: " . $e->getMessage()); }
        try { Artisan::call("config:clear"); } catch (\Exception $e) { Log::warning("config:clear failed: " . $e->getMessage()); }
        try { Artisan::call("route:clear"); } catch (\Exception $e) { Log::warning("route:clear failed: " . $e->getMessage()); }
        try { Artisan::call("view:clear"); } catch (\Exception $e) { Log::warning("view:clear failed: " . $e->getMessage()); }

        // If you use opcache and deployment method doesn't restart PHP-FPM/Apache
        // You might need to manually reset opcache or use a package for it.
        // if (function_exists('opcache_reset')) {
        //     opcache_reset();
        // }
    }
}