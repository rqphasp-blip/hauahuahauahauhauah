// validation.js - Script de validação em tempo real para o formulário de participação

$(document).ready(function() {
    // Configuração AJAX global
    $.ajaxSetup({
        timeout: 10000, // 10 segundos de timeout
        error: function(xhr, status, error) {
            console.error('Erro AJAX:', error);
            hideLoading();
            showStatus('Erro de conexão. Tente novamente.', 'danger');
        }
    });
    
    // Validação do Instagram quando o usuário sair do campo
    $('#instagram').on('blur', function() {
        const instagram = $(this).val().trim();
        if (instagram) {
            validateInstagram(instagram);
        }
    });
    
    // Validaço do CPF quando o usuário sair do campo
    $('#cpf').on('blur', function() {
        const cpf = $(this).val().trim();
        if (cpf && instagramVerified) {
            validateCPF(cpf);
        }
    });
    
    // Submissão do formulário
    $('#participacaoForm').on('submit', function(e) {
        e.preventDefault();
        if (validateForm()) {
            submitForm();
        }
    });
    
    // Formatação em tempo real do Instagram
    $('#instagram').on('input', function() {
        let value = $(this).val().replace(/[^a-zA-Z0-9._]/g, '');
        $(this).val(value);
        
        // Reset do status se o usurio modificar o campo
        if (instagramVerified) {
            instagramVerified = false;
            $('#cpf, #nome, #whatsapp, #email, #submitBtn').prop('disabled', true);
            $('#instagramStatus').html('');
        }
    });
    
    // Formatação em tempo real do CPF
    $('#cpf').on('input', function() {
        // Reset do status se o usuário modificar o campo
        if (cpfVerified) {
            cpfVerified = false;
            $('#nome, #whatsapp, #email, #submitBtn').prop('disabled', true);
            $('#cpfStatus').html('');
        }
    });
});

// Função para validar Instagram
function validateInstagram(instagram) {
    if (!instagram || instagram.length < 3) {
        $('#instagramStatus').html('<i class="fas fa-exclamation-triangle text-warning"></i> Instagram deve ter pelo menos 3 caracteres');
        return;
    }
    
    showLoading();
    $('#instagramStatus').html('<i class="fas fa-spinner fa-spin text-primary"></i> Verificando...');
    
    $.ajax({
        url: 'https://bio6.me/resources/forms/check_instagram.php',
        method: 'POST',
        dataType: 'json',
        data: { instagram: instagram },
        success: function(response) {
            hideLoading();
            
            if (response.success) {
                if (response.exists) {
                    // Instagram já existe
                    instagramVerified = false;
                    $('#instagramStatus').html(`<i class="fas fa-user-check text-info"></i> ${response.nome} já está participando!`);
                    showStatus(`Olá, <strong>${response.nome}</strong>! Você já está participando da promoção. Boa sorte! 🍀`, 'info');
                    
                    // Desabilitar todos os campos
                    $('#cpf, #nome, #whatsapp, #email, #submitBtn').prop('disabled', true);
                } else {
                    // Instagram disponível
                    instagramVerified = true;
                    $('#instagramStatus').html('<i class="fas fa-check-circle text-success"></i> Instagram disponível!');
                    
                    // Habilitar campo CPF
                    $('#cpf').prop('disabled', false).focus();
                    $('#statusMessage').addClass('d-none');
                }
            } else {
                $('#instagramStatus').html(`<i class="fas fa-exclamation-circle text-danger"></i> ${response.message}`);
            }
        }
    });
}

// Função para validar CPF
function validateCPF(cpf) {
    if (!isValidCPF(cpf)) {
        $('#cpfStatus').html('<i class="fas fa-exclamation-triangle text-warning"></i> CPF inválido');
        return;
    }
    
    showLoading();
    $('#cpfStatus').html('<i class="fas fa-spinner fa-spin text-primary"></i> Verificando...');
    
    $.ajax({
        url: 'https://bio6.me/resources/forms/check_cpf.php',
        method: 'POST',
        dataType: 'json',
        data: { cpf: cpf },
        success: function(response) {
            hideLoading();
            
            if (response.success) {
                if (response.exists) {
                    // CPF já existe
                    cpfVerified = false;
                    $('#cpfStatus').html(`<i class="fas fa-user-check text-info"></i> ${response.nome} já está participando!`);
                    showStatus(`Olá, <strong>${response.nome}</strong>! Este CPF já está participando da promoção. Boa sorte! 🍀`, 'info');
                    
                    // Desabilitar campos restantes
                    $('#nome, #whatsapp, #email, #submitBtn').prop('disabled', true);
                } else {
                    // CPF disponível
                    cpfVerified = true;
                    $('#cpfStatus').html('<i class="fas fa-check-circle text-success"></i> CPF disponível!');
                    
                    // Habilitar campos restantes
                    $('#nome, #whatsapp, #email, #submitBtn').prop('disabled', false);
                    $('#nome').focus();
                    $('#statusMessage').addClass('d-none');
                }
            } else {
                $('#cpfStatus').html(`<i class="fas fa-exclamation-circle text-danger"></i> ${response.message}`);
            }
        }
    });
}

// Função para validar CPF (algoritmo)
function isValidCPF(cpf) {
    cpf = cpf.replace(/[^\d]/g, '');
    
    if (cpf.length !== 11 || /^(\d)\1{10}$/.test(cpf)) {
        return false;
    }
    
    let sum = 0;
    for (let i = 0; i < 9; i++) {
        sum += parseInt(cpf.charAt(i)) * (10 - i);
    }
    
    let remainder = 11 - (sum % 11);
    if (remainder === 10 || remainder === 11) remainder = 0;
    if (remainder !== parseInt(cpf.charAt(9))) return false;
    
    sum = 0;
    for (let i = 0; i < 10; i++) {
        sum += parseInt(cpf.charAt(i)) * (11 - i);
    }
    
    remainder = 11 - (sum % 11);
    if (remainder === 10 || remainder === 11) remainder = 0;
    if (remainder !== parseInt(cpf.charAt(10))) return false;
    
    return true;
}

// Função para validar e-mail
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

// Função para validar WhatsApp
function isValidWhatsApp(whatsapp) {
    const cleanWhatsApp = whatsapp.replace(/[^\d]/g, '');
    return cleanWhatsApp.length >= 10 && cleanWhatsApp.length <= 11;
}

// Função para validar todo o formulário
function validateForm() {
    let isValid = true;
    let firstError = null;
    
    // Validar Instagram
    const instagram = $('#instagram').val().trim();
    if (!instagram || !instagramVerified) {
        showStatus('Por favor, verifique o Instagram primeiro.', 'warning');
        $('#instagram').focus();
        return false;
    }
    
    // Validar CPF
    const cpf = $('#cpf').val().trim();
    if (!cpf || !cpfVerified) {
        showStatus('Por favor, verifique o CPF primeiro.', 'warning');
        $('#cpf').focus();
        return false;
    }
    
    // Validar Nome
    const nome = $('#nome').val().trim();
    if (!nome || nome.length < 3) {
        showStatus('Por favor, informe seu nome completo.', 'warning');
        $('#nome').focus();
        return false;
    }
    
    // Validar WhatsApp
    const whatsapp = $('#whatsapp').val().trim();
    if (!whatsapp || !isValidWhatsApp(whatsapp)) {
        showStatus('Por favor, informe um WhatsApp válido.', 'warning');
        $('#whatsapp').focus();
        return false;
    }
    
    // Validar E-mail
    const email = $('#email').val().trim();
    if (!email || !isValidEmail(email)) {
        showStatus('Por favor, informe um e-mail válido.', 'warning');
        $('#email').focus();
        return false;
    }
    
    return true;
}

// Função para submeter o formulário
function submitForm() {
    showLoading();
    $('#submitBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Processando...');
    
    const formData = {
        instagram: $('#instagram').val().trim(),
        cpf: $('#cpf').val().trim(),
        nome: $('#nome').val().trim(),
        whatsapp: $('#whatsapp').val().trim(),
        email: $('#email').val().trim()
    };
    
    $.ajax({
        url: 'https://bio6.me/resources/forms/register_participant.php',
        method: 'POST',
        dataType: 'json',
        data: formData,
        success: function(response) {
            hideLoading();
            
            if (response.success) {
                // Sucesso no cadastro
                showStatus(`🎉 Parabéns, <strong>${formData.nome}</strong>! Você está participando da promoção a partir de agora. Boa sorte! 🍀`, 'success');
                
                // Disparar evento do Pixel do Meta
                if (typeof fbq !== 'undefined') {
                    fbq('track', 'CompleteRegistration', {
                        content_name: 'Participação em Promoço',
                        content_category: 'Promoção',
                        value: 1,
                        currency: 'BRL'
                    });
                }
                
                // Desabilitar formulário
                $('#participacaoForm input, #submitBtn').prop('disabled', true);
                $('#submitBtn').html('<i class="fas fa-check me-2"></i>Participação Confirmada!');
                
            } else {
                showStatus(`Erro: ${response.message}`, 'danger');
                $('#submitBtn').prop('disabled', false).html('<i class="fas fa-paper-plane me-2"></i>Participar da Promoção');
            }
        },
        error: function() {
            hideLoading();
            showStatus('Erro ao processar cadastro. Tente novamente.', 'danger');
            $('#submitBtn').prop('disabled', false).html('<i class="fas fa-paper-plane me-2"></i>Participar da Promoção');
        }
    });
}

