<?php
namespace RobertDevore\WPComCheck;

class WPComPluginHandler {
    private string $pluginSlug;
    private string $learnMoreLink;

    /**
     * Constructor to initialize plugin handler.
     *
     * @param string $pluginSlug    The plugin slug.
     * @param string $learnMoreLink The link to more information.
     */
    public function __construct( string $pluginSlug, string $learnMoreLink ) {
        $this->pluginSlug    = $pluginSlug;
        $this->learnMoreLink = $learnMoreLink;
        
        add_action( 'plugins_loaded', [ $this, 'autoDeactivate' ] );
        add_action( 'admin_notices', [ $this, 'showAdminNotice' ] );
        register_activation_hook( __FILE__, [ $this, 'activationCheck' ] );
        register_deactivation_hook( __FILE__, [ $this, 'setDeactivationFlag' ] );
    }

    /**
     * Checks if the plugin is running in a WordPress.com environment and handles deactivation if needed.
     *
     * @return bool True if the plugin was deactivated; otherwise, false.
     */
    public function pluginCheck(): bool {
        if ( defined( 'IS_WPCOM' ) && IS_WPCOM ) {
            if ( ! function_exists( 'deactivate_plugins' ) ) {
                require_once ABSPATH . 'wp-admin/includes/plugin.php';
            }

            if ( is_admin() ) {
                deactivate_plugins( $this->pluginSlug );
                add_option( 'wpcom_plugin_deactivation_notice', $this->learnMoreLink );
                return true;
            }
        }

        return false;
    }

    /**
     * Automatically deactivate the plugin if it is in an unsupported environment.
     *
     * @return void
     */
    public function autoDeactivate(): void {
        if ( $this->pluginCheck() ) {
            return;
        }
    }

    /**
     * Display an admin notice if the plugin was deactivated due to hosting restrictions.
     *
     * @return void
     */
    public function showAdminNotice(): void {
        $noticeLink = get_option( 'wpcom_plugin_deactivation_notice' );
        if ( $noticeLink ) {
            echo '<div class="notice notice-error">';
            echo '<p>' . wp_kses_post(
                sprintf(
                    __(
                        'The plugin has been deactivated because it cannot be used on WordPress.com-hosted websites. %s',
                        'wpcom-plugin-check'
                    ),
                    '<a href="' . esc_url( $noticeLink ) . '" target="_blank" rel="noopener">' . __( 'Learn more', 'wpcom-plugin-check' ) . '</a>'
                )
            ) . '</p>';
            echo '</div>';
            delete_option( 'wpcom_plugin_deactivation_notice' );
        }
    }

    /**
     * Prevent activation on WordPress.com-hosted sites.
     *
     * @return void
     */
    public function activationCheck(): void {
        if ( $this->pluginCheck() ) {
            wp_die(
                wp_kses_post(
                    sprintf(
                        '<h1>%s</h1><p>%s</p><p><a href="%s" target="_blank" rel="noopener">%s</a></p>',
                        __( 'Plugin Activation Blocked', 'wpcom-plugin-check' ),
                        __( 'This plugin cannot be activated on WordPress.com-hosted websites. It is restricted due to concerns about WordPress.com policies impacting the community.', 'wpcom-plugin-check' ),
                        esc_url( $this->learnMoreLink ),
                        __( 'Learn more', 'wpcom-plugin-check' )
                    )
                ),
                esc_html__('Plugin Activation Blocked', 'wpcom-plugin-check'),
                [ 'back_link' => true ]
            );
        }
    }

    /**
     * Set a flag when the plugin is deactivated.
     *
     * @return void
     */
    public function setDeactivationFlag(): void {
        add_option( 'wpcom_plugin_deactivation_notice', $this->learnMoreLink );
    }
}

// Initialize the class.
new WPComPluginHandler( plugin_basename( __FILE__ ), 'https://example.com/community-statement' );
