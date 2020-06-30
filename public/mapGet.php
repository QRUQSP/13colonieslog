<?php
//
// Description
// -----------
// This function will return the image binary data in jpg format.
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:         The ID of the tenant to get the image from.
// image_id:            The ID if the image requested.
// version:             The version of the image (original, thumbnail)
//
//                      *note* the thumbnail is not referring to the size, but to a 
//                      square cropped version, designed for use as a thumbnail.
//                      This allows only a portion of the original image to be used
//                      for thumbnails, as some images are too complex for thumbnails.
//
// maxwidth:            The max width of the longest side should be.  This allows
//                      for generation of thumbnail's, etc.
//
// maxlength:           The max length of the longest side should be.  This allows
//                      for generation of thumbnail's, etc.
//
// Returns
// -------
// Binary image data
//
function qruqsp_13colonieslog_mapGet(&$ciniki) {
    //
    // Check args
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbQuote');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'), 
//        'sections'=>array('required'=>'no', 'blank'=>'yes', 'type'=>'list', 'name'=>'Sections'), 
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args = $rc['args'];

    //
    // Make sure this module is activated, and
    // check permission to run this function for this tenant
    //
    ciniki_core_loadMethod($ciniki, 'qruqsp', '13colonieslog', 'private', 'checkAccess');
    $rc = qruqsp_13colonieslog_checkAccess($ciniki, $args['tnid'], 'qruqsp.13colonieslog.mapGet');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Get the list of sections
    //
    $strsql = "SELECT DISTINCT section "
        . "FROM qruqsp_13colonieslog_qsos "
        . "WHERE qruqsp_13colonieslog_qsos.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "AND YEAR(qso_dt) = 2020 "
        . "ORDER BY section "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbQueryList');
    $rc = ciniki_core_dbQueryList($ciniki, $strsql, 'qruqsp.13colonieslog', 'sections', 'section');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $sections = isset($rc['sections']) ? $rc['sections'] : array();

    //
    // Check the current map sections
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbDetailsQuery');
    $rc = ciniki_core_dbDetailsQuery($ciniki, 'qruqsp_13colonieslog_settings', 'tnid', $args['tnid'], 'qruqsp.13colonieslog', 'settings', '');
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'qruqsp.13colonieslog.18', 'msg'=>'', 'err'=>$rc['err']));
    }
    $settings = isset($rc['settings']) ? $rc['settings'] : array();

    //
    // Get the current map sections
    //
    $cache_map_sections = '';
    if( isset($settings['cache_map_sections']) && $settings['cache_map_sections'] != '' ) {
        $cache_map_sections = $settings['cache_map_sections'];
    }

    //
    // Check cache
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'tenants', 'hooks', 'cacheDir');
    $rc = ciniki_tenants_hooks_cacheDir($ciniki, $args['tnid'], array());
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'qruqsp.13colonieslog.26', 'msg'=>'', 'err'=>$rc['err']));
    }
    if( !is_dir($rc['cache_dir']) ) {
        mkdir($rc['cache_dir'], 0755, true);
    }
    $cache_file = $rc['cache_dir'] . '/13coloniesmap.jpg';
    

    if( is_array($sections) && implode(',', $sections) == $cache_map_sections && file_exists($cache_file)) {
        $map = new Imagick($cache_file);
    } else {
/*        $map = imagecreatefrompng($ciniki['config']['qruqsp.core']['modules_dir'] . '/13colonieslog/maps/back_with_lines.png');
        if( isset($args['sections'][0]) && $args['sections'][0] != '' ) {
            foreach($args['sections'] as $s) {
                if( file_exists($ciniki['config']['qruqsp.core']['modules_dir'] . '/13colonieslog/maps/' . $s . '.png') ) {
                    $overlay = imagecreatefrompng($ciniki['config']['qruqsp.core']['modules_dir'] . '/13colonieslog/maps/' . $s . '.png');
                    imagecolortransparent($overlay, 
                    $overlay->paintTransparentImage("rgb(111,196,249)", 0, 3000);
                    $map->compositeImage($overlay, Imagick::COMPOSITE_DEFAULT, 0, 0);
                }
            }
        }
        $map->setImageFormat('jpeg');
        $map->setImageCompressionQuality(60);
        $map->writeImage($cache_file); 
*/
        $map = new Imagick($ciniki['config']['qruqsp.core']['modules_dir'] . '/13colonieslog/maps/back_with_lines.png');
      
        if( count($sections) > 0 ) {
            foreach($sections as $s) {
                if( file_exists($ciniki['config']['qruqsp.core']['modules_dir'] . '/13colonieslog/maps/' . $s . '.png') ) {
                    $overlay = new Imagick($ciniki['config']['qruqsp.core']['modules_dir'] . '/13colonieslog/maps/' . $s . '.png');
                    $map->compositeImage($overlay, Imagick::COMPOSITE_DEFAULT, 0, 0);
                }
            }
        }
        $map->setImageFormat('jpeg');
        $map->setImageCompressionQuality(60);
        $map->writeImage($cache_file); 

        //
        // Update the settings
        //
        $strsql = "INSERT INTO qruqsp_13colonieslog_settings (tnid, detail_key, detail_value, date_added, last_updated) "
            . "VALUES ('" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "'"
            . ", 'cache_map_sections'"
            . ", '" . ciniki_core_dbQuote($ciniki, implode(',', $sections)) . "'"
            . ", UTC_TIMESTAMP(), UTC_TIMESTAMP()) "
            . "ON DUPLICATE KEY UPDATE detail_value = '" . ciniki_core_dbQuote($ciniki, implode(',', $sections)) . "' "
            . ", last_updated = UTC_TIMESTAMP() "
            . "";
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbInsert');
        $rc = ciniki_core_dbInsert($ciniki, $strsql, 'qruqsp.13colonieslog');
        if( $rc['stat'] != 'ok' ) {
            ciniki_core_dbTransactionRollback($ciniki, 'qruqsp.13colonieslog');
            return $rc;
        }
//        $ciniki['session']['qruqsp.13colonieslog']['map_sections'] = $sections;
//        sort($ciniki['session']['qruqsp.13colonieslog']['map_sections']);
    }

    header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT', true, 200);
    header("Content-type: image/jpeg"); 

    echo $map;
    
    return array('stat'=>'exit');
}
?>
