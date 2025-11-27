<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AdminController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Auth\SocialLoginController;
use App\Http\Controllers\LinkTypeViewController;
use App\Http\Controllers\PagesController;
use App\Http\Controllers\InstallerController;
use App\Http\Controllers\Admin\PluginController;
use App\Http\Controllers\AppearanceSettingsController; // Adicionado para a nova rota
use App\Http\Controllers\VerificationBadgeController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Exemplo de rotas para o gerenciador de plugins (adicione ao seu routes/web.php ou arquivo de rotas admin)
// Certifique-se de proteger estas rotas com middleware de autenticação e admin, se aplicável.
// Rotas para o gerenciamento do banner de perfil
// Forma correta usando ::class





use App\Providers\plugins\googlereviews\GooglereviewsController;
use App\Providers\plugins\leads01\Leads01Controller;

Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/leads01', [Leads01Controller::class, 'index'])->name('leads01.index');
    Route::get('/leads01/create', [Leads01Controller::class, 'create'])->name('leads01.create');
    Route::post('/leads01', [Leads01Controller::class, 'store'])->name('leads01.store');
    Route::get('/leads01/{id}/edit', [Leads01Controller::class, 'edit'])->name('leads01.edit');
    Route::put('/leads01/{id}', [Leads01Controller::class, 'update'])->name('leads01.update');
    Route::delete('/leads01/{id}', [Leads01Controller::class, 'destroy'])->name('leads01.destroy');
    Route::get('/leads01/{id}/leads', [Leads01Controller::class, 'leads'])->name('leads01.leads');
    Route::get('/leads01/{id}/leads/{entryId}', [Leads01Controller::class, 'showLead'])->name('leads01.leads.show');
	  Route::post('/leads01/{id}/toggle-visible', [Leads01Controller::class, 'toggleVisible'])
        ->name('leads01.campaign.toggle-visible');
});

Route::middleware(['web'])->group(function () {
    Route::get('/user/{username}/leads01', [Leads01Controller::class, 'publicList'])->name('leads01.public');
    Route::get('/leads01/form/{slug}', [Leads01Controller::class, 'publicForm'])->name('leads01.public.form');
    Route::post('/leads01/form/{slug}', [Leads01Controller::class, 'submit'])->name('leads01.public.submit');
});





use App\Providers\plugins\products\ProductsController;
Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/products', [ProductsController::class, 'index'])->name('products.index');
    Route::get('/products/categories', [ProductsController::class, 'categories'])->name('products.categories.index');
    Route::post('/products/categories', [ProductsController::class, 'storeCategory'])->name('products.categories.store');
    Route::put('/products/categories/{id}', [ProductsController::class, 'updateCategory'])->name('products.categories.update');
    Route::post('/products', [ProductsController::class, 'storeProduct'])->name('products.store');
    Route::put('/products/{id}', [ProductsController::class, 'updateProduct'])->name('products.update');
    Route::delete('/products/{id}', [ProductsController::class, 'destroyProduct'])->name('products.destroy');
    Route::post('/products/settings', [ProductsController::class, 'updateSettings'])->name('products.settings.update');
});

Route::get('/user/{username}/products', [ProductsController::class, 'publicCatalog'])->name('products.catalog');
Route::get('/user/{username}/products/customer', [ProductsController::class, 'customerLookup'])->name('products.customer.lookup');
Route::post('/user/{username}/products/orders', [ProductsController::class, 'storeOrder'])->name('products.orders.store');



use plugins\highlights\HighlightsController;

Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/highlights', [HighlightsController::class, 'index'])->name('highlights.index');
    Route::get('/highlights/create', [HighlightsController::class, 'create'])->name('highlights.create');
    Route::post('/highlights', [HighlightsController::class, 'store'])->name('highlights.store');
    Route::get('/highlights/{id}', [HighlightsController::class, 'show'])->name('highlights.show');
    Route::delete('/highlights/{id}', [HighlightsController::class, 'destroy'])->name('highlights.destroy');
});



// Rotas para o gerenciamento de avaliações do Google
Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/googlereviews', [GooglereviewsController::class, 'index'])->name('googlereviews.index');
    Route::get('/googlereviews/create', [GooglereviewsController::class, 'create'])->name('googlereviews.create');
    Route::post('/googlereviews', [GooglereviewsController::class, 'store'])->name('googlereviews.store');
    Route::get('/googlereviews/{id}', [GooglereviewsController::class, 'show'])->name('googlereviews.show');
    Route::put('/googlereviews/{id}', [GooglereviewsController::class, 'update'])->name('googlereviews.update');
    Route::delete('/googlereviews/{id}', [GooglereviewsController::class, 'destroy'])->name('googlereviews.destroy');
    Route::get('/googlereviews/widget/{place_id}', [GooglereviewsController::class, 'widget'])->name('googlereviews.widget');
});


Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/products', [ProductsController::class, 'index'])->name('products.index');
    Route::post('/products/categories', [ProductsController::class, 'storeCategory'])->name('products.categories.store');
    Route::post('/products', [ProductsController::class, 'storeProduct'])->name('products.store');
    Route::delete('/products/{id}', [ProductsController::class, 'destroyProduct'])->name('products.destroy');
    Route::post('/products/settings', [ProductsController::class, 'updateSettings'])->name('products.settings.update');
});

Route::get('/user/{username}/products', [ProductsController::class, 'publicCatalog'])->name('products.catalog');
Route::get('/user/{username}/products/customer', [ProductsController::class, 'customerLookup'])->name('products.customer.lookup');
Route::post('/user/{username}/products/orders', [ProductsController::class, 'storeOrder'])->name('products.orders.store');






use App\Providers\plugins\gallery\GalleryController;

Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/gallery', [GalleryController::class, 'index'])->name('gallery.index');
    Route::get('/gallery/create', [GalleryController::class, 'create'])->name('gallery.create');
    Route::post('/gallery', [GalleryController::class, 'store'])->name('gallery.store');
    Route::get('/gallery/{id}', [GalleryController::class, 'show'])->name('gallery.show');
    Route::delete('/gallery/{id}', [GalleryController::class, 'destroy'])->name('gallery.destroy');
    Route::get('/user/{username}/gallery', [GalleryController::class, 'userGallery'])->name('gallery.user');
});

Route::middleware(['web', 'auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/gallery', fn () => redirect()->route('gallery.index'))->name('gallery.index');
    Route::get('/gallery/create', fn () => redirect()->route('gallery.create'))->name('gallery.create');
    Route::get('/gallery/{id}', fn ($id) => redirect()->route('gallery.show', $id))->name('gallery.show');
});



use App\Providers\plugins\stories\StoriesController;
Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/stories', [StoriesController::class, 'index'])->name('stories.index');
    Route::get('/stories/create', [StoriesController::class, 'create'])->name('stories.create');
    Route::post('/stories', [StoriesController::class, 'store'])->name('stories.store');
    Route::get('/stories/{id}', [StoriesController::class, 'show'])->name('stories.show');
    Route::delete('/stories/{id}', [StoriesController::class, 'destroy'])->name('stories.destroy');
    Route::get('/user/{username}/stories', [StoriesController::class, 'userStories'])->name('stories.user');
});


use App\Providers\plugins\banner\ProfileBannerController;

Route::middleware(["web", "auth"])->group(function () {
    Route::get("/banner", [ProfileBannerController::class, "index"])->name("banner.index");
    Route::post("/banner", [ProfileBannerController::class, "store"])->name("banner.store");
    Route::delete("/banner", [ProfileBannerController::class, "destroy"])->name("banner.destroy");
});


Route::middleware(["auth", "is_admin"])->prefix("admin")->name("admin.")->group(function () {
    Route::prefix("plugins")->name("plugins.")->group(function () {
        Route::get("/", [PluginController::class, "index"])->name("index");
        Route::get("/create", [PluginController::class, "create"])->name("create");
        Route::post("/store", [PluginController::class, "store"])->name("store");
        Route::post("/{identifier}/activate", [PluginController::class, "activate"])->name("activate");
        Route::post("/{identifier}/deactivate", [PluginController::class, "deactivate"])->name("deactivate");
        Route::delete("/{identifier}/delete", [PluginController::class, "delete"])->name("delete");
    });
});



// Prevents section below from being run by 'composer update'
if(file_exists(base_path('storage/app/ISINSTALLED'))){
  // generates new APP KEY if no one is set
  if(EnvEditor::getKey('APP_KEY')==''){try{Artisan::call('key:generate');} catch (exception $e) {}}
 
  // copies template meta config if none is present
  if(!file_exists(base_path("config/advanced-config.php"))){copy(base_path('storage/templates/advanced-config.php'), base_path('config/advanced-config.php'));}
 }

 // Installer
if(file_exists(base_path('INSTALLING')) or file_exists(base_path('INSTALLERLOCK'))){

  Route::get('/', [InstallerController::class, 'showInstaller'])->name('showInstaller');
  Route::post('/create-admin', [InstallerController::class, 'createAdmin'])->name('createAdmin');
  Route::post('/db', [InstallerController::class, 'db'])->name('db');
  Route::post('/mysql', [InstallerController::class, 'mysql'])->name('mysql');
  Route::post('/options', [InstallerController::class, 'options'])->name('options');
  Route::get('/mysql-test', [InstallerController::class, 'mysqlTest'])->name('mysqlTest');
  Route::get('/skip', function () {Artisan::call('db:seed', ['--class' => 'AdminSeeder',]); return redirect(url(''));});
  Route::post('/editConfigInstaller', [InstallerController::class, 'editConfigInstaller'])->name('editConfigInstaller');

  Route::get('{any}', function() {
    if(!DB::table('users')->get()->isEmpty()){
    if(file_exists(base_path("INSTALLING")) and !file_exists(base_path('INSTALLERLOCK'))){unlink(base_path("INSTALLING"));header("Refresh:0");}
    } else {
      return redirect(url(''));
    }
  })->where('any', '.*');

}else{

// Disables routes if in Maintenance Mode
if(env('MAINTENANCE_MODE') != 'true'){

require __DIR__.'/home.php';

//Redirect if no page URL is set
Route::get('/@', function () {
    return redirect('/studio/no_page_name');
});

//Show diagnose page
Route::get('/panel/diagnose', function () {
        return view('panel/diagnose', []);
});

//Public route
$custom_prefix = config('advanced-config.custom_url_prefix');
Route::get('/going/{id?}', [UserController::class, 'clickNumber'])->where('link', '.*')->name('clickNumber')->middleware('disableCookies');
Route::get('/info/{id?}', [AdminController::class, 'redirectInfo'])->name('redirectInfo');
if($custom_prefix != ""){Route::get('/' . $custom_prefix . '{littlelink}', [UserController::class, 'littlelink'])->name('littlelink');}
Route::get('/@{littlelink}', [UserController::class, 'littlelink'])->name('littlelink')->middleware('disableCookies');
Route::get('/pages/'.strtolower(footer('Terms')), [AdminController::class, 'pagesTerms'])->name('pagesTerms')->middleware('disableCookies');
Route::get('/pages/'.strtolower(footer('Privacy')), [AdminController::class, 'pagesPrivacy'])->name('pagesPrivacy')->middleware('disableCookies');
Route::get('/pages/'.strtolower(footer('Contact')), [AdminController::class, 'pagesContact'])->name('pagesContact')->middleware('disableCookies');
Route::get('/theme/@{littlelink}', [UserController::class, 'theme'])->name('theme');
Route::get('/vcard/{id?}', [UserController::class, 'vcard'])->name('vcard');
Route::get('/u/{id?}', [UserController::class, 'userRedirect'])->name('userRedirect');

Route::get('/report', function () {return view('report');});
Route::post('/report', [UserController::class, 'report'])->name('report');

Route::get('/demo-page', [App\Http\Controllers\HomeController::class, 'demo'])->name('demo')->middleware('disableCookies');

Route::get('/block-asset/{type}', [LinkTypeViewController::class, 'blockAsset'])
  ->name('block.asset')->where(['type' => '[a-zA-Z0-9_-]+']);

}

Route::middleware(['auth', 'blocked', 'impersonate'])->group(function () {
//User route
Route::group([
    'middleware' => env('REGISTER_AUTH'),
], function () {
if(env('FORCE_ROUTE_HTTPS') == 'true'){URL::forceScheme('https');}
if(isset($_COOKIE['LinkCount'])){if($_COOKIE['LinkCount'] == '20'){$LinkPage = 'showLinks20';}elseif($_COOKIE['LinkCount'] == '30'){$LinkPage = 'showLinks30';}elseif($_COOKIE['LinkCount'] == 'all'){$LinkPage = 'showLinksAll';} else {$LinkPage = 'showLinks';}} else {$LinkPage = 'showLinks';} //Shows correct link number
Route::get('/dashboard', [AdminController::class, 'index'])->name('panelIndex');
Route::get('/studio/index', function(){return redirect(url('dashboard'));});
Route::get('/studio/add-link', [UserController::class, 'AddUpdateLink'])->name('showButtons');
Route::post('/studio/edit-link', [UserController::class, 'saveLink'])->name('addLink');
Route::get('/studio/edit-link/{id}', [UserController::class, 'AddUpdateLink'])->name('showLink')->middleware('link-id');
Route::post('/studio/sort-link', [UserController::class, 'sortLinks'])->name('sortLinks');
Route::get('/studio/links', [UserController::class, $LinkPage])->name($LinkPage);
Route::get('/studio/theme', [UserController::class, 'showTheme'])->name('showTheme');
Route::post('/studio/theme', [UserController::class, 'editTheme'])->name('editTheme');
Route::get('/deleteLink/{id}', [UserController::class, 'deleteLink'])->name('deleteLink')->middleware('link-id');
Route::get('/upLink/{up}/{id}', [UserController::class, 'upLink'])->name('upLink')->middleware('link-id');
Route::post('/studio/edit-link/{id}', [UserController::class, 'editLink'])->name('editLink')->middleware('link-id');
Route::get('/studio/button-editor/{id}', [UserController::class, 'showCSS'])->name('showCSS')->middleware('link-id');
Route::post('/studio/button-editor/{id}', [UserController::class, 'editCSS'])->name('editCSS')->middleware('link-id');
Route::get('/studio/page', [UserController::class, 'showPage'])->name('showPage');
Route::get('/studio/no_page_name', [UserController::class, 'showPage'])->name('showPage');
Route::post('/studio/page', [UserController::class, 'editPage'])->name('editPage');
Route::post('/studio/background', [UserController::class, 'themeBackground'])->name('themeBackground');
Route::get('/studio/rem-background', [UserController::class, 'removeBackground'])->name('removeBackground');
Route::get('/studio/profile', [UserController::class, 'showProfile'])->name('showProfile');
Route::post('/studio/profile', [UserController::class, 'editProfile'])->name('editProfile');
Route::post('/edit-icons', [UserController::class, 'editIcons'])->name('editIcons');
Route::get('/clearIcon/{id}', [UserController::class, 'clearIcon'])->name('clearIcon');
Route::get('/studio/page/delprofilepicture', [UserController::class, 'delProfilePicture'])->name('delProfilePicture');
Route::get('/studio/delete-user/{id}', [UserController::class, 'deleteUser'])->name('deleteUser')->middleware('verified');
Route::post('/auth-as', [AdminController::class, 'authAs'])->name('authAs');

// Nova rota para configurações de aparência
Route::post('/settings/appearance', [AppearanceSettingsController::class, 'update'])->name('settings.appearance.update');

// Catch all redirects
Route::get('/admin/users/all', fn() => redirect(route('showUsers')));
Route::get('/studio', fn() => redirect(url('dashboard')));
Route::get('/studio/edit-link', fn() => redirect(url('dashboard')));

if(env('ALLOW_USER_EXPORT') != false){
  Route::get('/export-links', [UserController::class, 'exportLinks'])->name('exportLinks');
  Route::get('/export-all', [UserController::class, 'exportAll'])->name('exportAll');
}
if(env('ALLOW_USER_IMPORT') != false){
  Route::post('/import-data', [UserController::class, 'importData'])->name('importData');
}
Route::get('/studio/linkparamform_part/{typeid}/{linkid}', [LinkTypeViewController::class, 'getParamForm'])->name('linkparamform.part');
});
});
}

//Social login route
Route::get('/social-auth/{provider}/callback', [SocialLoginController::class, 'providerCallback']);
Route::get('/social-auth/{provider}', [SocialLoginController::class, 'redirectToProvider'])->name('social.redirect');

Route::middleware(['auth', 'blocked', 'impersonate'])->group(function () {
//Admin route
Route::group([
    'middleware' => 'admin',
], function () {
    if(env('FORCE_ROUTE_HTTPS') == 'true'){URL::forceScheme('https');}
    Route::get('/panel/index', function(){return redirect(url('dashboard'));});
    Route::get('/admin/users', [AdminController::class, 'users'])->name('showUsers');
    Route::get('/admin/links/{id}', [AdminController::class, 'showLinksUser'])->name('showLinksUser');
    Route::get('/admin/deleteLink/{id}', [AdminController::class, 'deleteLinkUser'])->name('deleteLinkUser');
    Route::get('/admin/users/block/{block}/{id}', [AdminController::class, 'blockUser'])->name('blockUser');
    Route::get('/admin/users/verify/{verify}/{id}', [AdminController::class, 'verifyCheckUser'])->name('verifyCheckUser');
    Route::get('/admin/users/verify-mail/{verify}/{id}', [AdminController::class, 'verifyUser'])->name('verifyUser');
    Route::get('/admin/edit-user/{id}', [AdminController::class, 'showUser'])->name('showUser');
    Route::post('/admin/edit-user/{id}', [AdminController::class, 'editUser'])->name('editUser');
    Route::get('/admin/new-user', [AdminController::class, 'createNewUser'])->name('createNewUser')->middleware('max.users');
    Route::get('/admin/delete-user/{id}', [AdminController::class, 'deleteUser'])->name('deleteUser');
    Route::post('/admin/delete-table-user/{id}', [AdminController::class, 'deleteTableUser'])->name('deleteTableUser');
    Route::get('/admin/pages', [AdminController::class, 'showSitePage'])->name('showSitePage');
    Route::post('/admin/pages', [AdminController::class, 'editSitePage'])->name('editSitePage');
    Route::get('/admin/advanced-config', [AdminController::class, 'showFileEditor'])->name('showFileEditor');
    Route::post('/admin/advanced-config', [AdminController::class, 'editAC'])->name('editAC');
    Route::get('/admin/env', [AdminController::class, 'showFileEditor'])->name('showFileEditor');
    Route::post('/admin/env', [AdminController::class, 'editENV'])->name('editENV');
    Route::get('/admin/site', [AdminController::class, 'showSite'])->name('showSite');
    Route::post('/admin/site', [AdminController::class, 'editSite'])->name('editSite');
    Route::get('/admin/site/delavatar', [AdminController::class, 'delAvatar'])->name('delAvatar');
    Route::get('/admin/site/delfavicon', [AdminController::class, 'delFavicon'])->name('delFavicon');
    Route::get('/admin/phpinfo', [AdminController::class, 'phpinfo'])->name('phpinfo');
    Route::get('/admin/backups', [AdminController::class, 'showBackups'])->name('showBackups');
    Route::post('/admin/theme', [AdminController::class, 'deleteTheme'])->name('deleteTheme');
    Route::get('/admin/theme', [AdminController::class, 'showThemes'])->name('showThemes');
    Route::get('/update/theme', [AdminController::class, 'updateThemes'])->name('updateThemes');
    Route::get('/admin/config', [AdminController::class, 'showConfig'])->name('showConfig');
    Route::post('/admin/config', [AdminController::class, 'editConfig'])->name('editConfig');
    Route::get('/send-test-email', [AdminController::class, 'SendTestMail'])->name('SendTestMail');
    Route::get('/auth-as/{id}', [AdminController::class, 'authAsID'])->name('authAsID');
    Route::get('/theme-updater', function () {return view('studio/theme-updater', []);});
    Route::get('/update', function () {return view('update', []);});
    Route::get('/backup', function () {return view('backup', []);});
	Route::get('/admin/verification-badges', [VerificationBadgeController::class, 'index'])->name('verification-badges.index');
    Route::post('/admin/verification-badges', [VerificationBadgeController::class, 'store'])->name('verification-badges.store');
    Route::delete('/admin/verification-badges/{verificationBadge}', [VerificationBadgeController::class, 'destroy'])->name('verification-badges.destroy');

    Route::group(['namespace'=>'App\Http\Controllers\Admin', 'prefix'=>'admin', 'as'=>'admin'],function() {
        //Route::resource('/admin/linktype', LinkTypeController::class);
        Route::resources([
            'linktype'=>LinkTypeController::class
        ]);
    });

}); // End Admin authenticated routes
});

// Displays Maintenance Mode page
if(env('MAINTENANCE_MODE') == 'true'){
Route::get('/{any}', function () {
  return view('maintenance');
  })->where('any', '.*');
}

require __DIR__.'/auth.php';

if(config('advanced-config.custom_url_prefix') == ""){
  Route::get('/{littlelink}', [UserController::class, 'littlelink'])->name('littlelink');
}

// Rota para processar o upload do banner
Route::post('/perfil/banner/upload', function () {
    include_once base_path('banner_plugin_simples/includes/banner_upload_handler.php');
})->middleware('auth')->name('perfil.banner.upload');

// Rota para remover o banner
Route::post('/perfil/banner/remover', function () {
    include_once base_path('banner_plugin_simples/includes/banner_remove_handler.php');
})->middleware('auth')->name('perfil.banner.remover');
