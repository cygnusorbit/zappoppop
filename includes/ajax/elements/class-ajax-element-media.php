<?php
if (!defined('ABSPATH')) {
    exit;
}

class ZapPopPop_Ajax_Element_Media {

    public static function register() {
        $types = ['image', 'video', 'shape', 'divider', 'icon', 'html'];
        foreach ($types as $t) {
            ZapPopPop_Ajax_Element_Registry::register($t, [__CLASS__, 'sanitize_media']);
        }
    }

    public static function sanitize_media(array $el, array $clean) {
        // === ZAPPOPPOP_IMAGE_LIFECYCLE_SANITIZE ===
        $zappoppop_img_url = '';
        foreach (array('src', 'imageUrl', 'imgUrl') as $zappoppop_k) {
            if (isset($el[$zappoppop_k]) && !is_array($el[$zappoppop_k])) {
                $zappoppop_v = esc_url_raw(trim(wp_unslash((string) $el[$zappoppop_k])));
                if ($zappoppop_v !== '' && $zappoppop_img_url === '') {
                    $zappoppop_img_url = $zappoppop_v;
                }
            }
        }
        if ($zappoppop_img_url !== '' || isset($el['src']) || isset($el['imageUrl']) || isset($el['imgUrl'])) {
            $clean['src']      = $zappoppop_img_url;
            $clean['imageUrl'] = $zappoppop_img_url;
            $clean['imgUrl']   = $zappoppop_img_url;
        }
        if (isset($el['linkUrl']) && !is_array($el['linkUrl'])) {
            $clean['linkUrl'] = esc_url_raw(trim(wp_unslash((string) $el['linkUrl'])));
        }
        if (isset($el['linkTarget']) && !is_array($el['linkTarget'])) {
            $clean['linkTarget'] = ((string) $el['linkTarget'] === '_blank') ? '_blank' : '_self';
            $clean['targetBlank'] = $clean['linkTarget'] === '_blank';
            $clean['linkBlank'] = $clean['targetBlank'];
        }
        $zappoppop_alt = null;
        foreach (array('alt', 'altText', 'imageAlt') as $zappoppop_k) {
            if (isset($el[$zappoppop_k]) && !is_array($el[$zappoppop_k])) {
                $zappoppop_alt = sanitize_text_field(wp_unslash((string) $el[$zappoppop_k]));
                break;
            }
        }
        if ($zappoppop_alt !== null) {
            $clean['alt'] = $zappoppop_alt;
            $clean['altText'] = $zappoppop_alt;
            $clean['imageAlt'] = $zappoppop_alt;
        }
        $zappoppop_fit_ok = array('cover', 'contain', 'fill', 'none', 'scale-down');
        foreach (array('objectFit', 'imageFit') as $zappoppop_k) {
            if (isset($el[$zappoppop_k]) && !is_array($el[$zappoppop_k])) {
                $zappoppop_fit = sanitize_text_field((string) $el[$zappoppop_k]);
                if (in_array($zappoppop_fit, $zappoppop_fit_ok, true)) {
                    $clean['objectFit'] = $zappoppop_fit;
                    $clean['imageFit']  = $zappoppop_fit;
                    break;
                }
            }
        }
        // === END ZAPPOPPOP_IMAGE_LIFECYCLE_SANITIZE ===
        if (isset($el['imgUrl']))             $clean['imgUrl']             = esc_url_raw($el['imgUrl']);
        if (isset($el['altText']))            $clean['altText']            = sanitize_text_field($el['altText']);
        if (isset($el['objectFit']))          $clean['objectFit']          = sanitize_text_field($el['objectFit']);
        if (isset($el['videoUrl']))           $clean['videoUrl']           = esc_url_raw($el['videoUrl']);
        if (isset($el['videoThumb']) && !is_array($el['videoThumb'])) $clean['videoThumb'] = esc_url_raw(trim(wp_unslash((string) $el['videoThumb'])));
        if (isset($el['videoAutoplay'])) $clean['videoAutoplay'] = !in_array($el['videoAutoplay'], array(false, 'false', '0', 0, '', null, 'off'), true);
        if (isset($el['videoMute'])) $clean['videoMute'] = !in_array($el['videoMute'], array(false, 'false', '0', 0, '', null, 'off'), true);
        if (isset($el['videoLoop'])) $clean['videoLoop'] = !in_array($el['videoLoop'], array(false, 'false', '0', 0, '', null, 'off'), true);
        // ZAPPOPPOP_VIDEO_WHITELIST_V2
        $zappoppop_vbool = function ($v) { return !in_array($v, array(false, 'false', '0', 0, '', null, 'off'), true); };
        foreach (array('autoplay' => 'videoAutoplay', 'mute' => 'videoMute', 'muted' => 'videoMute', 'loop' => 'videoLoop', 'controls' => 'videoControls') as $zappoppop_src => $zappoppop_dst) {
            if (isset($el[$zappoppop_src]) && !is_array($el[$zappoppop_src]) && !isset($clean[$zappoppop_dst])) {
                $clean[$zappoppop_dst] = $zappoppop_vbool($el[$zappoppop_src]);
            }
        }
        if (isset($el['videoControls']) && !is_array($el['videoControls'])) $clean['videoControls'] = $zappoppop_vbool($el['videoControls']);
        if (isset($el['videoProvider']) && in_array($el['videoProvider'], array('youtube', 'vimeo', 'mp4'), true)) $clean['videoProvider'] = $el['videoProvider'];
        if (isset($el['aspectRatio']) && !in_array($el['aspectRatio'], array('16:9', '4:3', '1:1', '9:16'), true)) unset($el['aspectRatio']);
        if (isset($el['aspectRatio']))        $clean['aspectRatio']        = sanitize_text_field($el['aspectRatio']);
        if (isset($el['shapePreset']))        $clean['shapePreset']        = sanitize_key($el['shapePreset']);
        if (isset($el['shapeFill']))          $clean['shapeFill']          = ZapPopPop_Color::sanitize($el['shapeFill'], sanitize_text_field($el['shapeFill']));
        if (isset($el['shapeStroke']))        $clean['shapeStroke']        = ZapPopPop_Color::sanitize($el['shapeStroke'], sanitize_text_field($el['shapeStroke']));
        if (isset($el['shapeStrokeWidth']))   $clean['shapeStrokeWidth']   = intval($el['shapeStrokeWidth']);
        if (isset($el['shapeRotate']))        $clean['shapeRotate']        = intval($el['shapeRotate']);
        if (isset($el['shapeRadius']))        $clean['shapeRadius']        = min(500, max(0, intval($el['shapeRadius']))); /* ZPP_SHAPE_RADIUS */
        if (isset($el['shapeNoFill']))        $clean['shapeNoFill']        = !empty($el['shapeNoFill']) && $el['shapeNoFill'] !== 'false';
        if (isset($el['strokeWidth']))        $clean['strokeWidth']        = max(0, intval($el['strokeWidth']));
        if (isset($el['strokeNone']))         $clean['strokeNone']         = !empty($el['strokeNone']) && $el['strokeNone'] !== 'false';
        if (isset($el['strokeWidthPrev']))    $clean['strokeWidthPrev']    = max(0, intval($el['strokeWidthPrev']));
        if (isset($el['strokeColor']))        $clean['strokeColor']        = ZapPopPop_Color::sanitize($el['strokeColor'], sanitize_text_field($el['strokeColor']));
        if (isset($el['rotate']))             $clean['rotate']             = intval($el['rotate']);
        if (isset($el['bgColor']))            $clean['bgColor']            = ZapPopPop_Color::sanitize($el['bgColor'], sanitize_text_field($el['bgColor']));
        if (isset($el['transparentFill']))    $clean['transparentFill']    = !empty($el['transparentFill']) && $el['transparentFill'] !== 'false';
        if (isset($el['dividerStyle']))       $clean['dividerStyle']       = sanitize_text_field($el['dividerStyle']);
        if (isset($el['dividerColor']))       $clean['dividerColor']       = ZapPopPop_Color::sanitize($el['dividerColor'], sanitize_text_field($el['dividerColor']));
        if (isset($el['dividerThickness']))   $clean['dividerThickness']   = intval($el['dividerThickness']);
        if (isset($el['dividerOrientation'])) $clean['dividerOrientation'] = sanitize_text_field($el['dividerOrientation']);
        if (isset($el['iconGlyph']))          $clean['iconGlyph']          = sanitize_text_field($el['iconGlyph']);
        if (isset($el['iconSize']))           $clean['iconSize']           = intval($el['iconSize']);
        if (isset($el['iconColor']))          $clean['iconColor']          = ZapPopPop_Color::sanitize($el['iconColor'], sanitize_text_field($el['iconColor']));
        if (isset($el['dividerWeight']))      $clean['dividerWeight']      = max(1, intval($el['dividerWeight']));
        if (isset($el['dividerWidthPct']))    $clean['dividerWidthPct']    = min(100, max(1, intval($el['dividerWidthPct'])));
        if (isset($el['dividerAlign']))       $clean['dividerAlign']       = in_array($el['dividerAlign'], array('left','center','right'), true) ? $el['dividerAlign'] : 'center';
        if (isset($el['iconClass']))          $clean['iconClass']          = sanitize_text_field($el['iconClass']);
        return $clean;
    }
}
