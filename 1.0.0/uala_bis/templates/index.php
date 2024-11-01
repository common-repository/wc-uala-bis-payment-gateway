<?php
namespace uala_bis_WoocommercePaymentGateway;

$pluginInstance = uala_bis::get_instance();
?>

<div style="padding:25px; padding-left:0px;">
    <section id="header">
        <div class="sectionImage">
            <img style="width: 200px;" src='<?php echo esc_attr($pluginInstance->PLUGIN_LOGO)?>' alt='<?php echo esc_attr($pluginInstance->PARTNER_NAME) ?>'>
        </div>
        <div class="separator"></div>
        <!-- <div class="sectionTitle">
            <h1>Paso a paso</h1>
        </div> -->
    </section>
    <div id="steps">
    </div>
</div>
<script>
    let info = [
        {
            id: 1,
            number: '01',
            title: 'Integra tus credenciales',
            text: 'Te brinderemos una api key y una secret key para que puedas habilitar y testear las ventas.',
            cta: '¿Cómo conseguir mis credenciales?',
            ctaUrl: '<?php echo esc_url($pluginInstance->URL_CREDENTIALS_ACQUISITION) ?>'
        },
        {
            id: 2,
            number: '02',
            title: 'Activa nuestro medio de pago',
            text: 'Selecciona nuestra opción de checkout y haz click en Gestionar para abrir la pantalla de administración del plugin.'
        },
        {
            id: 3,
            number: '03',
            title: 'Paga desde nuestro checkout',
            text: 'Realiza los pagos desde nuestro checkout.',
            cta: 'Configurar plugin',
            ctaUrl: '<?php echo esc_url($pluginInstance->ADMIN_ROUTE) ?>'
        }
    ]

    const divSteps = document.getElementById("steps")

    info.forEach(element => {
        let div = document.createElement("div")
        div.classList.add("step")
        div.innerHTML = `
            <div class="stepHead">
                <div class="stepNumber">${element.number}</div>
                <div class="stepTitle">${element.title}</div>
            </div>
            <div class="stepText">${element.text}</div>
            ${element.cta !== undefined ? `
                <div class="stepCta">
                    <a href="${element.ctaUrl}">${element.cta}</a>
                </div>
            ` : ''}
        `
        divSteps.appendChild(div)
    })
</script>

<style>
    :root{
        --primary-color:<?php echo esc_attr($pluginInstance->PLUGIN_INFO_PAGE_MAIN_COLOR) ?>;
        --primary-color-light:<?php echo esc_attr($pluginInstance->PLUGIN_INFO_PAGE_MAIN_LIGHT_COLOR) ?>;
        --secondary-color:<?php echo esc_attr($pluginInstance->PLUGIN_INFO_PAGE_SECONDARY_COLOR) ?>;
        --secondary-color-light:<?php echo esc_attr($pluginInstance->PLUGIN_INFO_PAGE_SECONDARY_LIGHT_COLOR) ?>;
    }
</style>