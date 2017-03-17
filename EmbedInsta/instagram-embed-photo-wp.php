<?php
    /**
     * Plugin Name: Instagram Embed Photo WP
     * Version: 1.0 (alpha)
     * Plugin URI: https://wpembedphoto.000webhostapp.com/
     * Description: A plugin who creates an embed instagram photo container in a few simple steps.
     * Author: Leandro Nix
     * Author URI: https://about.me/leandro.nix
     * Text Domain: Instagram Embed
     * License: GPL v3
     */

    /**
     * Instagram Embed Photo WP
     * Copyright (C) 2017, Leandro Nix - nixlovemi@gmail.com
     *
     * This program is free software: you can redistribute it and/or modify
     * it under the terms of the GNU General Public License as published by
     * the Free Software Foundation, either version 3 of the License, or
     * (at your option) any later version.
     *
     * This program is distributed in the hope that it will be useful,
     * but WITHOUT ANY WARRANTY; without even the implied warranty of
     * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
     * GNU General Public License for more details.
     *
     * You should have received a copy of the GNU General Public License
     * along with this program.  If not, see <http://www.gnu.org/licenses/>.
     */

    class nixInstagramEmbedPhotoWp {
        // plugin folder: instagram-embed-photo-wp

        public static function init(){
            $InstagramEmbed = new nixInstagramEmbedPhotoWp();
            $InstagramEmbed->getEmbedHTML();
        }

        public static function initAdminPage(){
            add_menu_page('Settings - Instagram Embed', 'Instagram Embed Settings', 'administrator', 'instagram-embed-photo-wp-settings', 'nixInstagramEmbedPhotoWp::showAdminPage', 'dashicons-admin-generic');
        }

        public static function showAdminPage(){
            $pdfTokenPath = plugins_url("instagram-embed-photo-wp/How-create-an-instagram-app-and-get-the-access-token.pdf");
            ?>

            <div class="wrap">
            <h2>Instagram Embed Photo WP Settings</h2>

            <div class="card pressthis">
                <h2>How do I use this plugin?</h2>
                <p>For this plugin to work correctly you just need to tell me two things:</p>
                <p><strong>Instagram User Id:</strong> your Instagram user ID. If you don't know how to get this info, you could use this <a href="https://smashballoon.com/instagram-feed/find-instagram-user-id/" target="_blank">link</a> to get it.</p>
                <p><strong>Access Token:</strong> a little trickier to get than user ID. I believe that with <a href="<?php echo $pdfTokenPath; ?>" target="_blank">this guide</a> you'll get this information.</p>
                <p>After those information were filled, just use the shortcode [instagram_embed_photo_wp] anywhere you want the embed shows up.</p>
                <p>If you find any bug or want to make a suggestion, please email me: nixlovemi@gmail.com</p>
                <p>Anything else you may need could be find in the <a href="https://wpembedphoto.000webhostapp.com/" target="_blank">plugin's website</a>.</p>
            </div>

            <form method="post" action="options.php">
                <?php settings_fields( 'instagram-embed-photo-wp-group' ); ?>
                <?php do_settings_sections( 'instagram-embed-photo-wp-group' ); ?>
                <table class="form-table">
                    <tr valign="top">
                    <th scope="row">Instagram User ID</th>
                    <td><input type="text" name="insta_user_id" value="<?php echo esc_attr( get_option('insta_user_id') ); ?>" /></td>
                    </tr>

                    <tr valign="top">
                    <th scope="row">Instagram Token</th>
                    <td><input type="text" name="insta_token" value="<?php echo esc_attr( get_option('insta_token') ); ?>" /></td>
                    </tr>
                </table>

                <?php submit_button(); ?>

            </form>
            </div>

            <?php
        }

        public static function initSettings(){
            register_setting( 'instagram-embed-photo-wp-group', 'insta_user_id' );
            register_setting( 'instagram-embed-photo-wp-group', 'insta_token' );
        }

        private function curlExists(){
            return function_exists('curl_version');
        }

        private function getArrayInstaInfo($userId, $instaToken){
            $curlExists = $this->curlExists();
            if(!$curlExists){
                die("CURL extension not enabled!");
            }

            if (@session_id() == "") @session_start();
            $arrInstagram = $_SESSION["arrInstagram"];
            
            if(!is_array($arrInstagram) || count($arrInstagram) <= 0){
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_URL,"https://api.instagram.com/v1/users/$userId/media/recent/?access_token=$instaToken&count=1");
                $result=curl_exec($ch);
                curl_close($ch);

                $arrResp = json_decode($result, true);

                $urlImgInstagram = (isset($arrResp["data"][0]["images"]["standard_resolution"]["url"])) ? $arrResp["data"][0]["images"]["standard_resolution"]["url"] : "";
                $imgInstagram = (isset($arrResp["data"][0]["link"])) ? $arrResp["data"][0]["link"]: "";
                $txtInstagram = (isset($arrResp["data"][0]["caption"]["text"])) ? $arrResp["data"][0]["caption"]["text"]: "";

                $arrInstagram = array();
                $arrInstagram["urlImgInstagram"] = $urlImgInstagram;
                $arrInstagram["imgInstagram"] = $imgInstagram;
                $arrInstagram["txtInstagram"] = $txtInstagram;

                $_SESSION["arrInstagram"] = $arrInstagram;
            }

            return $_SESSION["arrInstagram"];
        }

        public function getEmbedHTML(){
            $instaUserId = get_option("insta_user_id");
            $instaToken = get_option("insta_token");
            $instaArrayInfo = $this->getArrayInstaInfo($instaUserId, $instaToken);

            $urlImgInstagram = (isset($instaArrayInfo["urlImgInstagram"])) ? $instaArrayInfo["urlImgInstagram"]: "";
            $imgInstagram = (isset($instaArrayInfo["imgInstagram"])) ? $instaArrayInfo["imgInstagram"]: "";
            $txtInstagram = (isset($instaArrayInfo["txtInstagram"])) ? $instaArrayInfo["txtInstagram"]: "";

            if($urlImgInstagram == "" || $imgInstagram == ""){
                die("No information found. Check the settings on wp-admin!");
            }

            $html = "<blockquote class='instagram-media' data-instgrm-captioned data-instgrm-version='7' style=' background:#FFF; border:0; border-radius:3px; box-shadow:0 0 1px 0 rgba(0,0,0,0.5),0 1px 10px 0 rgba(0,0,0,0.15); margin: 1px; max-width:658px; padding:0; width:99.375%; width:-webkit-calc(100% - 2px); width:calc(100% - 2px);'>
                        <div style='padding:8px;'>
                            <div style=' background:#F8F8F8; line-height:0; margin-top:40px; padding:62.5% 0; text-align:center; width:100%;'>
                                <div style=' background:url($urlImgInstagram); display:block; height:44px; margin:0 auto -44px; position:relative; top:-22px; width:44px;'></div>
                            </div>
                            <p style=' margin:8px 0 0 0; padding:0 4px;'>
                                <a href='$imgInstagram' style=' color:#000; font-family:Arial,sans-serif; font-size:14px; font-style:normal; font-weight:normal; line-height:17px; text-decoration:none; word-wrap:break-word;' target='_blank'>
                                    $txtInstagram
                                </a>
                            </p>
                            <!--<p style=' color:#c9c8cd; font-family:Arial,sans-serif; font-size:14px; line-height:17px; margin-bottom:0; margin-top:8px; overflow:hidden; padding:8px 0 7px; text-align:center; text-overflow:ellipsis; white-space:nowrap;'>
                                CentroMusical Morumbi (@centromusicalmorumbi)
                            </p>-->
                        </div>
                     </blockquote>
                     <script async defer src='//platform.instagram.com/en_US/embeds.js'></script>";
            echo $html;

        }

    }

    // init the wordpress hooks
    add_shortcode('instagram_embed_photo_wp', 'nixInstagramEmbedPhotoWp::init');
    add_action('admin_menu', 'nixInstagramEmbedPhotoWp::initAdminPage');
    add_action('admin_init', 'nixInstagramEmbedPhotoWp::initSettings');