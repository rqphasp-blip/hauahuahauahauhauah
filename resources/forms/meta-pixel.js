// meta-pixel.js - Integração com Meta Pixel (Facebook Pixel)

// Configuração do Meta Pixel
const META_PIXEL_ID = 'SEU_PIXEL_ID_AQUI'; // Substitua pelo seu Pixel ID

// Função para inicializar o Meta Pixel
function initMetaPixel(pixelId) {
    if (typeof fbq !== 'undefined') {
        console.log('Meta Pixel já inicializado');
        return;
    }
    
    // Código base do Meta Pixel
    !function(f,b,e,v,n,t,s)
    {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
    n.callMethod.apply(n,arguments):n.queue.push(arguments)};
    if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
    n.queue=[];t=b.createElement(e);t.async=!0;
    t.src=v;s=b.getElementsByTagName(e)[0];
    s.parentNode.insertBefore(t,s)}(window, document,'script',
    'https://connect.facebook.net/en_US/fbevents.js');
    
    // Inicializar o pixel
    fbq('init', pixelId);
    
    // Disparar evento PageView
    fbq('track', 'PageView');
    
    console.log('Meta Pixel inicializado com ID:', pixelId);
}

// Função para rastrear abertura do modal
function trackModalOpen() {
    if (typeof fbq !== 'undefined') {
        fbq('track', 'InitiateCheckout', {
            content_name: 'Modal Participação Promoção',
            content_category: 'Promoção',
            value: 1,
            currency: 'BRL'
        });
        
        console.log('Meta Pixel: Modal aberto');
    }
}

// Função para rastrear início do preenchimento
function trackFormStart() {
    if (typeof fbq !== 'undefined') {
        fbq('track', 'Lead', {
            content_name: 'Início Preenchimento Formulário',
            content_category: 'Promoção',
            value: 1,
            currency: 'BRL'
        });
        
        console.log('Meta Pixel: Início do formulário');
    }
}

// Função para rastrear validação de Instagram
function trackInstagramValidation(isValid, exists = false) {
    if (typeof fbq !== 'undefined') {
        if (isValid && !exists) {
            fbq('trackCustom', 'InstagramValidated', {
                content_name: 'Instagram Validado',
                content_category: 'Promoção',
                status: 'valid_new'
            });
        } else if (exists) {
            fbq('trackCustom', 'InstagramExists', {
                content_name: 'Instagram Já Cadastrado',
                content_category: 'Promoção',
                status: 'already_registered'
            });
        }
        
        console.log('Meta Pixel: Instagram validado -', isValid ? 'válido' : 'inválido', exists ? '(já existe)' : '');
    }
}

// Função para rastrear validação de CPF
function trackCPFValidation(isValid, exists = false) {
    if (typeof fbq !== 'undefined') {
        if (isValid && !exists) {
            fbq('trackCustom', 'CPFValidated', {
                content_name: 'CPF Validado',
                content_category: 'Promoção',
                status: 'valid_new'
            });
        } else if (exists) {
            fbq('trackCustom', 'CPFExists', {
                content_name: 'CPF Já Cadastrado',
                content_category: 'Promoção',
                status: 'already_registered'
            });
        }
        
        console.log('Meta Pixel: CPF validado -', isValid ? 'válido' : 'inválido', exists ? '(já existe)' : '');
    }
}

// Função para rastrear conclusão do cadastro
function trackRegistrationComplete(participantName) {
    if (typeof fbq !== 'undefined') {
        fbq('track', 'CompleteRegistration', {
            content_name: 'Participação em Promoção',
            content_category: 'Promoção',
            value: 1,
            currency: 'BRL',
            status: 'completed'
        });
        
        // Evento customizado adicional
        fbq('trackCustom', 'PromoParticipation', {
            content_name: 'Nova Participação',
            participant_name: participantName,
            event_type: 'registration_success'
        });
        
        console.log('Meta Pixel: Cadastro concluído para', participantName);
    }
}

// Função para rastrear erros
function trackError(errorType, errorMessage) {
    if (typeof fbq !== 'undefined') {
        fbq('trackCustom', 'FormError', {
            content_name: 'Erro no Formulário',
            error_type: errorType,
            error_message: errorMessage
        });
        
        console.log('Meta Pixel: Erro rastreado -', errorType, errorMessage);
    }
}

// Função para rastrear abandono do formulário
function trackFormAbandonment(lastField) {
    if (typeof fbq !== 'undefined') {
        fbq('trackCustom', 'FormAbandonment', {
            content_name: 'Abandono do Formulário',
            last_field: lastField,
            content_category: 'Promoção'
        });
        
        console.log('Meta Pixel: Abandono do formulário no campo:', lastField);
    }
}

// Função para rastrear tempo no formulário
function trackTimeOnForm(timeInSeconds) {
    if (typeof fbq !== 'undefined' && timeInSeconds > 10) { // Só rastreia se passou mais de 10 segundos
        fbq('trackCustom', 'FormEngagement', {
            content_name: 'Tempo no Formulário',
            time_spent: timeInSeconds,
            engagement_level: timeInSeconds > 60 ? 'high' : timeInSeconds > 30 ? 'medium' : 'low'
        });
        
        console.log('Meta Pixel: Tempo no formulário:', timeInSeconds, 'segundos');
    }
}

// Inicializar o Meta Pixel quando o documento estiver pronto
$(document).ready(function() {
    // Inicializar o pixel (substitua pelo seu ID real)
    if (META_PIXEL_ID !== 'SEU_PIXEL_ID_AQUI') {
        initMetaPixel(META_PIXEL_ID);
    } else {
        console.warn('Meta Pixel ID não configurado. Substitua SEU_PIXEL_ID_AQUI pelo seu Pixel ID real.');
    }
    
    // Variáveis para rastreamento de tempo
    let formStartTime = null;
    let lastActiveField = null;
    
    // Rastrear abertura do modal
    $('#participacaoModal').on('show.bs.modal', function() {
        trackModalOpen();
        formStartTime = Date.now();
    });
    
    // Rastrear início do preenchimento
    $('#instagram').on('focus', function() {
        if (formStartTime) {
            trackFormStart();
        }
    });
    
    // Rastrear campo ativo para abandono
    $('#participacaoForm input').on('focus', function() {
        lastActiveField = $(this).attr('name');
    });
    
    // Rastrear fechamento do modal (possível abandono)
    $('#participacaoModal').on('hide.bs.modal', function() {
        if (formStartTime && !$('#submitBtn').prop('disabled')) {
            const timeSpent = Math.round((Date.now() - formStartTime) / 1000);
            trackFormAbandonment(lastActiveField);
            trackTimeOnForm(timeSpent);
        }
    });
    
    // Rastrear saída da página (abandono)
    $(window).on('beforeunload', function() {
        if (formStartTime && $('#participacaoModal').hasClass('show') && !$('#submitBtn').prop('disabled')) {
            const timeSpent = Math.round((Date.now() - formStartTime) / 1000);
            trackFormAbandonment(lastActiveField);
            trackTimeOnForm(timeSpent);
        }
    });
});

// Exportar funções para uso global
window.MetaPixelTracking = {
    trackInstagramValidation,
    trackCPFValidation,
    trackRegistrationComplete,
    trackError,
    trackFormAbandonment,
    trackTimeOnForm
};

