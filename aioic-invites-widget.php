<?php
defined( 'ABSPATH' ) || exit;

 class Aioic_Invites_Widget extends  WP_Widget{


    function __construct()
    {
        parent::__construct("aioic_invites_widget", __("AIOIC Invites",'all-in-one-invite-codes-buddypress'), array('description'=>__("Show the current member Invites Status.",'all-in-one-invite-codes-buddypress')), );


    }

     public function widget( $args, $instance ) {
         $title = apply_filters( 'widget_title', $instance['title'] );
         echo $args['before widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- sidebar markup registered by the theme.

         if(is_user_logged_in()){
            $cuser = wp_get_current_user();
             $args = array(
                 'author'         => get_current_user_id(),
                 'posts_per_page' => - 1,
                 'post_type'      => 'tk_invite_codes', //you can use also 'any'
             );
             $active_codes = array();          
             $used_codes   = array();
             $the_query = new WP_Query( $args );
             if ( $the_query->have_posts() ) {
                 while ( $the_query->have_posts() ) : $the_query->the_post();
                     $all_in_one_invite_codes_options = get_post_meta( get_the_ID(), 'all_in_one_invite_codes_options', true );
                     $code_amount                     = isset( $all_in_one_invite_codes_options['generate_codes'] ) ? intval($all_in_one_invite_codes_options['generate_codes']) : 1;
			         $is_multiple_use				  = isset( $all_in_one_invite_codes_options['multiple_use'] ) ? true : false;
                     $code_total                      = isset( $all_in_one_invite_codes_options['code_total'] ) ? intval($all_in_one_invite_codes_options['code_total']) : $code_amount;

                     if($is_multiple_use){
                        $code = get_post_meta( get_the_ID(), 'tk_all_in_one_invite_code', true );
                        if($code_amount <= 0){
                            update_post_meta( get_the_ID(), 'tk_all_in_one_invite_code_status', 'Used' );
                            $code_amount = 0;

                        }
                        $current_amount = $code_total-$code_amount;
                        $active_codes[] = sprintf('<li> %s(%d/%d) </li>',esc_html($code),$code_amount,$code_total);
                        if($current_amount > 0){
                            $used_codes[]   = sprintf(' <li> %s(%d/%d) </li>',esc_html($code),$current_amount,$code_total);
                        }
                        
                     }
                    
                    
                     

                 endwhile;
             }
             //if title is present
             If ( ! empty ( $title ) )
                 Echo $args['before_title'] . esc_html($cuser->display_name).' : '.wp_kses_post($title) . $args['after_title']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- before_title/after_title are sidebar markup registered by the theme.
          echo '<div id="tabs">
              <ul>
                <li style="width:45%;"><a href="#tabs-1">'.esc_html__("Active",'all-in-one-invite-codes-buddypress').'</a></li>
              
                <li style="width:45%;"><a href="#tabs-2">'.esc_html__("Used",'all-in-one-invite-codes-buddypress').'</a></li>
              </ul>
              <div id="tabs-1"><ul>'.wp_kses_post(implode("",$active_codes)).'</ul></div>
              <div id="tabs-2"><ul>'.wp_kses_post(implode("",$used_codes)) .'</ul></div>
            </div>';
                echo ' <script>
             jQuery( function() {
                 jQuery( "#tabs" ).tabs();
             } );
               </script>';
       }else{
             If ( ! empty ( $title ) )
                 Echo $args['before_title'] .wp_kses_post($title) . $args['after_title']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- before_title/after_title are sidebar markup registered by the theme.

             echo '<a href="'.esc_url(wp_login_url()).'">'.esc_html__('Please log in','all-in-one-invite-codes-buddypress').'</a>' ;
         }


        echo $args['after_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- sidebar markup registered by the theme.
     }

     public function form( $instance ) {
         if ( isset( $instance[ 'title' ] ) )
             $title = $instance[ 'title' ];
         else
             $title = __( 'Invites Codes', 'all-in-one-invite-codes-buddypress' );
         ?>
         <p>
             <label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title:' ); // phpcs:ignore WordPress.WP.I18n.MissingArgDomain -- reuses the WordPress core string. ?></label>
             <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
         </p>
         <?php
     }





}