<?php
/**
 * Visual identity in the theme.
 *
 * The SciFlow WP plugin owns the brand settings (SciFlow → Configurações →
 * Identidade visual) and prints them as CSS variables (--brand-*). This file
 * maps Bootstrap and the theme onto those tokens, offers the same settings in
 * the Customizer, and keeps neutral fallbacks when the plugin is inactive.
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Brand values with fallbacks (plugin inactive → option or neutral defaults).
 *
 * @return array<string,string>
 */
function inscricao_enfrute_brand()
{
    if (class_exists('SciFlow_Branding')) {
        return SciFlow_Branding::get();
    }
    $o = get_option('sciflow_settings', array());
    $hex = function ($k, $d) use ($o) {
        $v = isset($o[$k]) ? sanitize_hex_color($o[$k]) : '';
        return $v ? $v : $d;
    };
    return array(
        'primary'          => $hex('brand_primary', '#1f6f78'),
        'primary_dark'     => $hex('brand_primary_dark', '#17545b'),
        'primary_light'    => '#e4eeef',
        'primary_contrast' => '#ffffff',
        'primary_rgb'      => '31, 111, 120',
        'secondary'        => $hex('brand_secondary', '#c27c0e'),
        'text'             => $hex('brand_text', '#1d2327'),
        'background'       => $hex('brand_background', '#ffffff'),
        'heading_font'     => '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif',
        'body_font'        => '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif',
        'radius'           => '8px',
        'logo_url'         => '',
        'logo_height'      => '56px',
    );
}

/**
 * Bootstrap and theme overrides on top of the brand tokens.
 */
function inscricao_enfrute_brand_css()
{
    $css = '';
    if (!class_exists('SciFlow_Branding')) {
        $b = inscricao_enfrute_brand();
        $css .= ':root{--brand-primary:' . $b['primary'] . ';--brand-primary-dark:' . $b['primary_dark'] . ';--brand-primary-light:' . $b['primary_light']
            . ';--brand-primary-contrast:' . $b['primary_contrast'] . ';--brand-primary-rgb:' . $b['primary_rgb'] . ';--brand-secondary:' . $b['secondary']
            . ';--brand-secondary-contrast:#ffffff;--brand-text:' . $b['text'] . ';--brand-muted:#6b7175;--brand-border:#dcdfe0;--brand-background:' . $b['background']
            . ';--brand-surface:#f5f8f8;--brand-heading-font:' . $b['heading_font'] . ';--brand-body-font:' . $b['body_font'] . ';--brand-radius:8px;--brand-logo-height:56px;}';
    }
    $css .= <<<'CSS'
:root{
  --bs-primary:var(--brand-primary);--bs-primary-rgb:var(--brand-primary-rgb);
  --bs-primary-text-emphasis:var(--brand-primary-dark);--bs-primary-bg-subtle:var(--brand-primary-light);--bs-primary-border-subtle:rgba(var(--brand-primary-rgb),.35);
  --bs-link-color:var(--brand-primary);--bs-link-color-rgb:var(--brand-primary-rgb);--bs-link-hover-color:var(--brand-primary-dark);
  --bs-body-color:var(--brand-text);--bs-body-bg:var(--brand-background);--bs-body-font-family:var(--brand-body-font);
  --bs-border-radius:var(--brand-radius);--bs-border-radius-lg:calc(var(--brand-radius) * 1.5);--bs-border-radius-sm:calc(var(--brand-radius) * .6);
  --bs-focus-ring-color:rgba(var(--brand-primary-rgb),.25);
}
body{font-family:var(--brand-body-font);color:var(--brand-text);background-color:var(--brand-background);}
h1,h2,h3,h4,h5,h6,.h1,.h2,.h3,.h4,.h5,.h6{font-family:var(--brand-heading-font);}
.btn-primary{--bs-btn-bg:var(--brand-primary);--bs-btn-border-color:var(--brand-primary);--bs-btn-color:var(--brand-primary-contrast);
  --bs-btn-hover-bg:var(--brand-primary-dark);--bs-btn-hover-border-color:var(--brand-primary-dark);--bs-btn-hover-color:var(--brand-primary-contrast);
  --bs-btn-active-bg:var(--brand-primary-dark);--bs-btn-active-border-color:var(--brand-primary-dark);--bs-btn-active-color:var(--brand-primary-contrast);
  --bs-btn-disabled-bg:var(--brand-primary);--bs-btn-disabled-border-color:var(--brand-primary);--bs-btn-disabled-color:var(--brand-primary-contrast);
  --bs-btn-focus-shadow-rgb:var(--brand-primary-rgb);}
.btn-outline-primary{--bs-btn-color:var(--brand-primary);--bs-btn-border-color:var(--brand-primary);--bs-btn-hover-bg:var(--brand-primary);
  --bs-btn-hover-border-color:var(--brand-primary);--bs-btn-hover-color:var(--brand-primary-contrast);--bs-btn-active-bg:var(--brand-primary-dark);
  --bs-btn-active-border-color:var(--brand-primary-dark);--bs-btn-active-color:var(--brand-primary-contrast);--bs-btn-focus-shadow-rgb:var(--brand-primary-rgb);}
.form-control:focus,.form-select:focus{border-color:var(--brand-primary);box-shadow:0 0 0 .25rem rgba(var(--brand-primary-rgb),.2);}
.form-check-input:checked{background-color:var(--brand-primary);border-color:var(--brand-primary);}
.sf-text-accent{color:var(--brand-secondary)!important;}
.site-branding{flex-shrink:0;max-width:45%;}
.site-branding img,.custom-logo{display:block;max-height:var(--brand-logo-height);max-width:100%;width:auto;height:auto;}
.site-branding .site-title{font-family:var(--brand-heading-font);font-weight:800;font-size:1.25rem;line-height:1.2;color:var(--brand-text);text-decoration:none;white-space:nowrap;}
CSS;
    return $css;
}

/**
 * Enqueue: the theme stylesheets depend on the plugin's 'sciflow-brand'
 * handle when it exists, and the overrides are attached after Bootstrap.
 */
function inscricao_enfrute_brand_enqueue()
{
    wp_add_inline_style('inscricao-enfrute-main', inscricao_enfrute_brand_css());
}
add_action('wp_enqueue_scripts', 'inscricao_enfrute_brand_enqueue', 20);

/**
 * Site logo: Customizer logo → SciFlow brand logo → site name.
 */
function inscricao_enfrute_logo()
{
    if (has_custom_logo()) {
        the_custom_logo();
        return;
    }
    $b = inscricao_enfrute_brand();
    echo '<a href="' . esc_url(home_url('/')) . '" rel="home" class="site-logo-link">';
    if (!empty($b['logo_url'])) {
        echo '<img src="' . esc_url($b['logo_url']) . '" alt="' . esc_attr(get_bloginfo('name')) . '">';
    } else {
        echo '<span class="site-title">' . esc_html(get_bloginfo('name')) . '</span>';
    }
    echo '</a>';
}

/**
 * Block editor palette follows the brand (replaces the fixed theme.json palette).
 */
add_filter('wp_theme_json_data_theme', function ($theme_json) {
    if (!method_exists($theme_json, 'update_with')) {
        return $theme_json;
    }
    $b = inscricao_enfrute_brand();
    return $theme_json->update_with(array(
        'version'  => 2,
        'settings' => array(
            'color' => array(
                'palette' => array(
                    array('slug' => 'brand-primary', 'color' => $b['primary'], 'name' => __('Principal', 'inscricao-enfrute')),
                    array('slug' => 'brand-primary-dark', 'color' => $b['primary_dark'], 'name' => __('Principal escura', 'inscricao-enfrute')),
                    array('slug' => 'brand-secondary', 'color' => $b['secondary'], 'name' => __('Destaque', 'inscricao-enfrute')),
                    array('slug' => 'brand-text', 'color' => $b['text'], 'name' => __('Texto', 'inscricao-enfrute')),
                    array('slug' => 'brand-background', 'color' => $b['background'], 'name' => __('Fundo', 'inscricao-enfrute')),
                ),
            ),
        ),
    ));
});

/**
 * Customizer: "Identidade visual" writes to the same option the plugin uses
 * (sciflow_settings[...]), so both screens always show the same values.
 */
add_action('customize_register', function (WP_Customize_Manager $wp_customize) {
    if (!current_user_can('manage_options')) {
        return;
    }
    $wp_customize->add_section('sciflow_identity', array(
        'title'       => __('Identidade visual (SciFlow)', 'inscricao-enfrute'),
        'description' => __('Cores, fontes e logo do evento. Também aplicados aos e-mails, certificados e Anais. Equivale a SciFlow → Configurações → Identidade visual.', 'inscricao-enfrute'),
        'priority'    => 25,
    ));

    $defaults = array('brand_primary' => '#1f6f78', 'brand_secondary' => '#c27c0e', 'brand_text' => '#1d2327', 'brand_background' => '#ffffff');
    $labels = array(
        'brand_primary'    => __('Cor principal', 'inscricao-enfrute'),
        'brand_secondary'  => __('Cor de destaque', 'inscricao-enfrute'),
        'brand_text'       => __('Cor do texto', 'inscricao-enfrute'),
        'brand_background' => __('Cor de fundo', 'inscricao-enfrute'),
    );
    foreach ($labels as $key => $label) {
        $id = 'sciflow_settings[' . $key . ']';
        $wp_customize->add_setting($id, array(
            'type'              => 'option',
            'capability'        => 'manage_options',
            'default'           => $defaults[$key],
            'sanitize_callback' => 'sanitize_hex_color',
            'transport'         => 'refresh',
        ));
        $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, $id, array('label' => $label, 'section' => 'sciflow_identity')));
    }

    $wp_customize->add_setting('sciflow_settings[brand_logo_id]', array(
        'type' => 'option', 'capability' => 'manage_options', 'default' => 0, 'sanitize_callback' => 'absint', 'transport' => 'refresh',
    ));
    $wp_customize->add_control(new WP_Customize_Media_Control($wp_customize, 'sciflow_settings[brand_logo_id]', array(
        'label' => __('Logo do evento', 'inscricao-enfrute'), 'section' => 'sciflow_identity', 'mime_type' => 'image',
        'description' => __('Usado no site quando não houver "Logo" em Identidade do site, e nos e-mails e Anais.', 'inscricao-enfrute'),
    )));

    if (class_exists('SciFlow_Branding')) {
        $choices = array();
        foreach (SciFlow_Branding::fonts() as $k => $f) {
            $choices[$k] = $f[0];
        }
        foreach (array('brand_heading_font' => __('Fonte dos títulos', 'inscricao-enfrute'), 'brand_body_font' => __('Fonte do texto', 'inscricao-enfrute')) as $key => $label) {
            $wp_customize->add_setting('sciflow_settings[' . $key . ']', array(
                'type' => 'option', 'capability' => 'manage_options', 'default' => 'system-sans', 'transport' => 'refresh',
                'sanitize_callback' => function ($v) use ($choices) { return isset($choices[$v]) ? $v : 'system-sans'; },
            ));
            $wp_customize->add_control('sciflow_settings[' . $key . ']', array('type' => 'select', 'label' => $label, 'section' => 'sciflow_identity', 'choices' => $choices));
        }
    }

    $wp_customize->add_setting('sciflow_settings[brand_radius]', array(
        'type' => 'option', 'capability' => 'manage_options', 'default' => 8, 'transport' => 'refresh',
        'sanitize_callback' => function ($v) { return min(24, max(0, absint($v))); },
    ));
    $wp_customize->add_control('sciflow_settings[brand_radius]', array(
        'type' => 'range', 'label' => __('Arredondamento dos cantos (px)', 'inscricao-enfrute'), 'section' => 'sciflow_identity',
        'input_attrs' => array('min' => 0, 'max' => 24, 'step' => 2),
    ));
});

/**
 * Keep the plugin's cached values in sync after Customizer saves.
 */
add_action('customize_save_after', function () {
    if (class_exists('SciFlow_Settings')) {
        SciFlow_Settings::flush();
    }
    if (class_exists('SciFlow_Branding')) {
        SciFlow_Branding::flush();
    }
});
