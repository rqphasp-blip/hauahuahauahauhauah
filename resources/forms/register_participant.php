<?php
// register_participant.php - API para registrar novo participante

require_once 'config.php';

try {
    // Verificar se é POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        jsonResponse(false, 'Método não permitido');
    }
    
    // Obter dados do POST
    $instagram = $_POST['instagram'] ?? '';
    $cpf = $_POST['cpf'] ?? '';
    $nome = $_POST['nome'] ?? '';
    $whatsapp = $_POST['whatsapp'] ?? '';
    $email = $_POST['email'] ?? '';
    
    // Sanitizar dados
    $instagram = sanitizeInput($instagram);
    $cpf = sanitizeInput($cpf);
    $nome = sanitizeInput($nome);
    $whatsapp = sanitizeInput($whatsapp);
    $email = sanitizeInput($email);
    
    // Remove @ do Instagram se presente
    $instagram = ltrim($instagram, '@');
    
    // Validações
    $errors = [];
    
    if (empty($instagram) || !isValidInstagram($instagram)) {
        $errors[] = 'Instagram inválido';
    }
    
    if (empty($cpf) || !isValidCPF($cpf)) {
        $errors[] = 'CPF inválido';
    }
    
    if (empty($nome) || strlen($nome) < 3) {
        $errors[] = 'Nome deve ter pelo menos 3 caracteres';
    }
    
    if (empty($whatsapp) || strlen(preg_replace('/[^0-9]/', '', $whatsapp)) < 10) {
        $errors[] = 'WhatsApp inválido';
    }
    
    if (empty($email) || !isValidEmail($email)) {
        $errors[] = 'E-mail inválido';
    }
    
    if (!empty($errors)) {
        jsonResponse(false, implode(', ', $errors));
    }
    
    // Conectar ao banco
    $db = new Database();
    $conn = $db->getConnection();
    
    // Iniciar transação
    $conn->beginTransaction();
    
    try {
        // Verificar novamente se Instagram e CPF não existem (double-check)
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM participantes WHERE instagram = :instagram OR cpf = :cpf");
        $stmt->bindParam(':instagram', $instagram);
        $stmt->bindParam(':cpf', $cpf);
        $stmt->execute();
        
        $result = $stmt->fetch();
        
        if ($result['count'] > 0) {
            $conn->rollBack();
            jsonResponse(false, 'Instagram ou CPF já cadastrado');
        }
        
        // Inserir novo participante
        $stmt = $conn->prepare("
            INSERT INTO participantes (nome, cpf, whatsapp, email, instagram, ip_cadastro, user_agent) 
            VALUES (:nome, :cpf, :whatsapp, :email, :instagram, :ip_cadastro, :user_agent)
        ");
        
        $ip_cadastro = getClientIP();
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':cpf', $cpf);
        $stmt->bindParam(':whatsapp', $whatsapp);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':instagram', $instagram);
        $stmt->bindParam(':ip_cadastro', $ip_cadastro);
        $stmt->bindParam(':user_agent', $user_agent);
        
        $stmt->execute();
        
        // Confirmar transação
        $conn->commit();
        
        // Log de sucesso
        logError('Novo participante cadastrado com sucesso', [
            'nome' => $nome,
            'instagram' => $instagram,
            'ip' => $ip_cadastro
        ]);
        
        jsonResponse(true, 'Participante cadastrado com sucesso', [
            'participant_id' => $conn->lastInsertId(),
            'nome' => $nome
        ]);
        
    } catch (Exception $e) {
        // Rollback em caso de erro
        $conn->rollBack();
        throw $e;
    }
    
} catch (Exception $e) {
    logError('Erro ao registrar participante: ' . $e->getMessage(), [
        'nome' => $nome ?? '',
        'instagram' => $instagram ?? '',
        'ip' => getClientIP()
    ]);
    
    if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
        jsonResponse(false, 'Instagram ou CPF já cadastrado');
    } else {
        jsonResponse(false, 'Erro interno do servidor');
    }
}
?>

