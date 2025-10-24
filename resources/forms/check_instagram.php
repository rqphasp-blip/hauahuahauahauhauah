<?php
// check_instagram.php - API para verificar se o Instagram já está cadastrado

require_once 'config.php';

try {
    // Verificar se é POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        jsonResponse(false, 'Método não permitido');
    }
    
    // Obter dados do POST
    $instagram = $_POST['instagram'] ?? '';
    
    // Validar Instagram
    $instagram = sanitizeInput($instagram);
    $instagram = ltrim($instagram, '@'); // Remove @ se presente
    
    if (empty($instagram)) {
        jsonResponse(false, 'Instagram é obrigatório');
    }
    
    if (!isValidInstagram($instagram)) {
        jsonResponse(false, 'Instagram inválido');
    }
    
    // Conectar ao banco
    $db = new Database();
    $conn = $db->getConnection();
    
    // Verificar se o Instagram já existe
    $stmt = $conn->prepare("SELECT nome FROM participantes WHERE instagram = :instagram LIMIT 1");
    $stmt->bindParam(':instagram', $instagram);
    $stmt->execute();
    
    $result = $stmt->fetch();
    
    if ($result) {
        // Instagram já existe
        jsonResponse(true, 'Instagram já cadastrado', [
            'exists' => true,
            'nome' => $result['nome']
        ]);
    } else {
        // Instagram disponível
        jsonResponse(true, 'Instagram disponível', [
            'exists' => false
        ]);
    }
    
} catch (Exception $e) {
    logError('Erro ao verificar Instagram: ' . $e->getMessage(), [
        'instagram' => $instagram ?? '',
        'ip' => getClientIP()
    ]);
    
    jsonResponse(false, 'Erro interno do servidor');
}
?>

