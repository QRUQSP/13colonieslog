<?php
//
// Description
// -----------
// This function will process a web request.
//
// Arguments
// ---------
// ciniki:
// settings:        The web settings structure.
// tnid:            The ID of the tenant to get page details for.
//
// args:            The possible arguments for the page
//
//
// Returns
// -------
//
function qruqsp_13colonieslog_web_processRequest(&$ciniki, $settings, $tnid, $args) {

    //
    // Check to make sure the module is enabled
    //
    if( !isset($ciniki['tenant']['modules']['qruqsp.13colonieslog']) ) {
        return array('stat'=>'404', 'err'=>array('code'=>'qruqsp.13colonieslog.5', 'msg'=>"I'm sorry, the page you requested does not exist."));
    }

    $page = array(
        'title'=>$args['page_title'],
        'breadcrumbs'=>$args['breadcrumbs'],
        'blocks'=>array(),
        );

    if( count($page['breadcrumbs']) == 0 ) {
        if( isset($settings['page-13colonieslog-name']) && $settings['page-13colonieslog-name'] != '' ) {
            $page['breadcrumbs'][] = array('name'=>$settings['page-13colonieslog-name'], 'url'=>$args['base_url']);
        } else {
            $page['breadcrumbs'][] = array('name'=>'Field Day', 'url'=>$args['base_url']);
        }
    }

   
    //
    // Display map
    //
/*    if( isset($args['uri_split'][0]) && $args['uri_split'][0] == 'map' ) {
        //
        // Get the list of sections
        //
        $strsql = "SELECT DISTINCT section "
            . "FROM qruqsp_13colonieslog_qsos "
            . "WHERE qruqsp_13colonieslog_qsos.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
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
        $rc = ciniki_core_dbDetailsQuery($ciniki, 'qruqsp_13colonieslog_settings', 'tnid', $tnid, 'qruqsp.13colonieslog', 'settings', '');
        if( $rc['stat'] != 'ok' ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'qruqsp.13colonieslog.15', 'msg'=>'', 'err'=>$rc['err']));
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
        $rc = ciniki_tenants_hooks_cacheDir($ciniki, $tnid, array());
        if( $rc['stat'] != 'ok' ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'qruqsp.13colonieslog.27', 'msg'=>'', 'err'=>$rc['err']));
        }
        if( !is_dir($rc['cache_dir']) ) {
            mkdir($rc['cache_dir'], 0755, true);
        }
        if( !is_dir($ciniki['tenant']['web_cache_dir']) ) {
            mkdir($ciniki['tenant']['web_cache_dir'], 0755, true);
        }
        $cache_file = $rc['cache_dir'] . '/fielddaymap.jpg';
        $web_cache_file = $ciniki['tenant']['web_cache_dir'] . '/fielddaymap.jpg';
        if( is_array($sections) && implode(',', $sections) == $cache_map_sections && file_exists($cache_file)) {
            $map = new Imagick($cache_file);
        } else {
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
                . "VALUES ('" . ciniki_core_dbQuote($ciniki, $tnid) . "'"
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
        }

        //
        // Check if web cache file needs updating
        //
        if( !file_exists($web_cache_file) || filemtime($web_cache_file) < filemtime($cache_file) ) {
            copy($cache_file, $web_cache_file);
        }

        //
        // Add the image to the page blocks
        //
        $block_content = "<div id='image-wrap' class='image-wrap'>";
        $block_content .= "<div class='image'><img src='" . $ciniki['tenant']['web_cache_url'] . '/fielddaymap.jpg' . "'/></div>";
        $block_content .= "</div>";
        $page['blocks'][] = array('type'=>'content', 'html'=>$block_content);
    } 
   
    //
    // Display sections
    //
    elseif( isset($args['uri_split'][0]) && $args['uri_split'][0] == 'sections' ) {
        //
        // Load the sections
        //
        ciniki_core_loadMethod($ciniki, 'qruqsp', '13colonieslog', 'private', 'sectionsLoad');
        $rc = qruqsp_13colonieslog_sectionsLoad($ciniki, $tnid);
        if( $rc['stat'] != 'ok' ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'qruqsp.13colonieslog.22', 'msg'=>'', 'err'=>$rc['err']));
        }
        $sections = $rc['sections'];
        $areas = $rc['areas'];
        
        //
        // Get the list of sections
        //
        $strsql = "SELECT DISTINCT section "
            . "FROM qruqsp_13colonieslog_qsos "
            . "WHERE qruqsp_13colonieslog_qsos.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
            . "AND YEAR(qso_dt) = 2020 "
            . "ORDER BY section "
            . "";
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbQueryList');
        $rc = ciniki_core_dbQueryList($ciniki, $strsql, 'qruqsp.13colonieslog', 'sections', 'section');
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        foreach($rc['sections'] as $s) {
            if( isset($sections[$s]) ) {
                $sections[$s]['num_qsos'] = 1;
            }
        }
        $block_content = '<div class="table"><table><thead>';
        $block_content .= '<tr>'
            . '<td class="aligncenter">DX</th>'
            . '<td class="aligncenter">1</th>'
            . '<td class="aligncenter">2</th>'
            . '<td class="aligncenter">3</th>'
            . '<td class="aligncenter">4</th>'
            . '<td class="aligncenter">5</th>'
            . '<td class="aligncenter">6</th>'
            . '<td class="aligncenter">7</th>'
            . '<td class="aligncenter">8</th>'
            . '<td class="aligncenter">9</th>'
            . '<td class="aligncenter">0</th>'
            . '<td class="aligncenter">CA</th>'
            . '</tr></thead><tbody>';
        for($i = 0; $i < 13; $i++) {    
            $block_content .= '<tr>';
            foreach($areas as $aid => $area) {
                if( isset($area['sections'][$i]['label']) ) {
                    if( $sections[$area['sections'][$i]['label']]['num_qsos'] > 0 ) {
                        $block_content .= '<td class="aligncenter statusgreen" style="background: #ddffdd;">' . $area['sections'][$i]['label'] . '</td>';
                    } else {
                        $block_content .= '<td class="aligncenter statusgreen">' . $area['sections'][$i]['label'] . '</td>';
                    }
                } else {
                    $block_content .= '<td></td>';
                }
            }
            $block_content .= '</tr>';
        }
        $block_content .= '</tbody></table></div>';
        $page['blocks'][] = array('type'=>'content', 'html'=>$block_content);
    } 
   
    //
    // Display the list of QSO's
    //
    else { */
        //
        // Get the list of qsos
        //
        $strsql = "SELECT qruqsp_13colonieslog_qsos.id, "
            . "qruqsp_13colonieslog_qsos.qso_dt, "
            . "DATE_FORMAT(qruqsp_13colonieslog_qsos.qso_dt, '%b %d %H:%i') AS qso_dt_display, "
            . "qruqsp_13colonieslog_qsos.callsign, "
            . "qruqsp_13colonieslog_qsos.class, "
            . "qruqsp_13colonieslog_qsos.section, "
            . "qruqsp_13colonieslog_qsos.band, "
            . "qruqsp_13colonieslog_qsos.mode, "
            . "qruqsp_13colonieslog_qsos.frequency, "
            . "qruqsp_13colonieslog_qsos.operator "
            . "FROM qruqsp_13colonieslog_qsos "
            . "WHERE qruqsp_13colonieslog_qsos.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
            . "AND YEAR(qso_dt) = 2020 "
            . "ORDER BY qso_dt DESC "
            . "";
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
        $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'qruqsp.13colonieslog', array(
            array('container'=>'qsos', 'fname'=>'id', 
                'fields'=>array('id', 'qso_dt', 'qso_dt_display', 'callsign', 'class', 'section', 'band', 'mode', 'frequency', 'operator'),
                ),
            ));
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        $qsos = isset($rc['qsos']) ? $rc['qsos'] : array();

        $page['blocks'][] = array('type'=>'table', 
            'header' => 'yes',
            'columns' => array( 
                array('label' => 'Date/Time', 'class' => 'alignleft', 'field' => 'qso_dt_display'),
                array('label' => 'Call Sign', 'class' => 'alignleft', 'field' => 'callsign'),
                array('label' => 'Class', 'class' => 'aligncenter', 'field' => 'class'),
                array('label' => 'Section', 'class' => 'aligncenter', 'field' => 'section'),
                array('label' => 'Band', 'class' => 'aligncenter', 'field' => 'band'),
                array('label' => 'Mode', 'class' => 'aligncenter', 'field' => 'mode'),
                array('label' => 'Frequency', 'class' => 'alignright', 'field' => 'frequency'),
                ),
            'rows' => $qsos,
            );
/*    } */

    //
    // Build the submenu
    //
//    $page['submenu'] = array(
//        'qsos' => array('name' => 'Contacts', 'url'=>$args['base_url']),
//        'sections' => array('name' => 'Sections', 'url'=>$args['base_url'] . '/sections'),
//        'map' => array('name' => 'Map', 'url'=>$args['base_url'] . '/map'),
//        'stats' => array('name' => 'Stats', 'url'=>$args['base_url'] . '/stats'),
//        );

    return array('stat'=>'ok', 'page'=>$page);
}
?>
