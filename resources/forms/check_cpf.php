<?php
// check_cpf.php - API para verificar se o CPF já está cadastrado

require_once 'config.php';

try {
    // Verificar se é POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        jsonResponse(false, 'Método não permitido');
    }
    
    // Obter dados do POST
    $cpf = $_POST['cpf'] ?? '';
    
    // Validar CPF
    $cpf = sanitizeInput($cpf);
    
    if (empty($cpf)) {
        jsonResponse(false, 'CPF é obrigatório');
    }
    
    if (!isValidCPF($cpf)) {
        jsonResponse(false, 'CPF inválido');
    }
    
    // Conectar ao banco
    $db = new Database();
    $conn = $db->getConnection();
    
    // Verificar se o CPF já existe
    $stmt = $conn->prepare("SELECT nome FROM participantes WHERE cpf = :cpf LIMIT 1");
    $stmt->bindParam(':cpf', $cpf);
    $stmt->execute();
    
    $result = $stmt->fetch();
    
    if ($result) {
        // CPF já existe
        jsonResponse(true, 'CPF já cadastrado', [
            'exists' => true,
            'nome' => $result['nome']
        ]);
    } else {
        // CPF disponível
        jsonResponse(true, 'CPF disponível', [
            'exists' => false
        ]);
    }
    
} catch (Exception $e) {
    logError('Erro ao verificar CPF: ' . $e->getMessage(), [
        'cpf' => substr($cpf ?? '', 0, 3) . '.***.***-**', // Log parcial por segurança
        'ip' => getClientIP()
    ]);
    
    jsonResponse(false, 'Erro interno do servidor');
}
?>

