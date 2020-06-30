<?php
//
// Description
// -----------
// This method will return everything for the UI for 13 Colonies Contest Logger
//
// Cabrillo spec found at: http://wwrof.org/cabrillo/cabrillo-specification-v3/
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:        The ID of the tenant to get QSO for.
//
// Returns
// -------
//
function qruqsp_13colonieslog_exportCabrillo($ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args = $rc['args'];

    //
    // Check access to tnid as owner, or sys admin.
    //
    ciniki_core_loadMethod($ciniki, 'qruqsp', '13colonieslog', 'private', 'checkAccess');
    $rc = qruqsp_13colonieslog_checkAccess($ciniki, $args['tnid'], 'qruqsp.13colonieslog.qsoList');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Load the settings
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbDetailsQuery');
    $rc = ciniki_core_dbDetailsQuery($ciniki, 'qruqsp_13colonieslog_settings', 'tnid', $args['tnid'], 'qruqsp.13colonieslog', 'settings', '');
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'qruqsp.13colonieslog.9', 'msg'=>'', 'err'=>$rc['err']));
    }
    $settings = isset($rc['settings']) ? $rc['settings'] : array();

    //
    // Load the date format strings for the user
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'users', 'private', 'datetimeFormat');
    $datetime_format = ciniki_users_datetimeFormat($ciniki, 'php');

    //
    // Get the list of qsos
    //
    $strsql = "SELECT qruqsp_13colonieslog_qsos.id, "
        . "qruqsp_13colonieslog_qsos.qso_dt, "
        . "DATE_FORMAT(qruqsp_13colonieslog_qsos.qso_dt, '%Y-%m-%d %H%i') AS qso_dt_display, "
        . "qruqsp_13colonieslog_qsos.callsign, "
        . "qruqsp_13colonieslog_qsos.recv_rst, "
        . "qruqsp_13colonieslog_qsos.recv_state_country, "
        . "qruqsp_13colonieslog_qsos.sent_rst, "
        . "qruqsp_13colonieslog_qsos.band, "
        . "qruqsp_13colonieslog_qsos.mode, "
        . "qruqsp_13colonieslog_qsos.frequency, "
        . "qruqsp_13colonieslog_qsos.operator "
        . "FROM qruqsp_13colonieslog_qsos "
        . "WHERE qruqsp_13colonieslog_qsos.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "AND YEAR(qso_dt) = 2020 "
        . "ORDER BY qso_dt ASC "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
    $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'qruqsp.13colonieslog', array(
        array('container'=>'qsos', 'fname'=>'id', 
            'fields'=>array('id', 'qso_dt', 'qso_dt_display', 'callsign', 'recv_rst', 'recv_state_country', 'sent_rst', 'band', 'mode', 'frequency', 'operator'),
            ),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $qsos = isset($rc['qsos']) ? $rc['qsos'] : array();

    //
    // Process QSOs
    //
    $cabrillo_qsos = '';
    $bands = array();
    $modes = array();
    $qso_points = 0;
    foreach($qsos as $qso) {
        if( !in_array($qso['band'], $bands) ) {
            $bands[] = $qso['band'];
        }
        if( !in_array($qso['mode'], $modes) ) {
            $modes[] = $qso['mode'];
        }
        if( $qso['mode'] == 'CW' || $qso['mode'] == 'DIG' ) {
            $qso_points += 2;
        } else {
            $qso_points += 1;
        }
        $cabrillo_qsos .= "QSO:";
        if( $qso['frequency'] != '' ) {
            $qso['frequency'] = preg_replace("/[^0-9]/", "", $qso['frequency']);
        } else {
            switch($qso['band']) {
                case 160: $qso['frequency'] = 1800; break;
                case 80: $qso['frequency'] = 3500; break;
                case 40: $qso['frequency'] = 7000; break;
                case 20: $qso['frequency'] = 14000; break;
                case 15: $qso['frequency'] = 21000; break;
                case 10: $qso['frequency'] = 28000; break;
                case 6: $qso['frequency'] = 50; break;
                case 2: $qso['frequency'] = 144; break;
                case 220: $qso['frequency'] = 222; break;
                case 440: $qso['frequency'] = 70; break;
            }
        }
        $cabrillo_qsos .= sprintf("%6s", $qso['frequency']);

        $cabrillo_qsos .= " " . $qso['mode'];
        $cabrillo_qsos .= " " . $qso['qso_dt_display'];
        $cabrillo_qsos .= sprintf(" %-13s", (isset($settings['callsign']) ? $settings['callsign'] : ''));
        $cabrillo_qsos .= sprintf(" %3s", $qso['sent_rst']);
        $cabrillo_qsos .= sprintf(" %-6s", (isset($settings['state']) ? $settings['state'] : ''));
        $cabrillo_qsos .= sprintf(" %-13s", $qso['callsign']);
        $cabrillo_qsos .= sprintf(" %3s", $qso['recv_rst']);
        $cabrillo_qsos .= sprintf(" %-7s", $qso['recv_state_country']);
        $cabrillo_qsos .= "\r\n";
    }

    $cabrillo = '';
    $cabrillo .= "START-OF-LOG: 3.0\r\n";
    $cabrillo .= "LOCATION: " . (isset($settings['state']) ? $settings['state'] : '') . "\r\n";
    $cabrillo .= "CALLSIGN: " . (isset($settings['callsign']) ? $settings['callsign'] : '') . "\r\n";
    $cabrillo .= "CONTEST: 13 COLONIES\r\n";
    $cabrillo .= "CLUB: " . (isset($settings['club']) ? $settings['club'] : '') . "\r\n";
    $cabrillo .= "CATEGORY-OPERATOR: " . (isset($settings['category-operator']) ? $settings['category-operator'] : '') . "\r\n";
    if( count($bands) > 1 ) {
        $cabrillo .= "CATEGORY-BAND: ALL\r\n";
    } elseif( count($bands) == 1 ) {
        $cabrillo .= "CATEGORY-BAND: " . $bands[0] . "\r\n";
    } else {
        $cabrillo .= "CATEGORY-BAND: \r\n";
    }
    if( count($modes) > 1 ) {
        $cabrillo .= "CATEGORY-MODE: MIXED\r\n";
    } else {
        $cabrillo .= "CATEGORY-MODE: " . (isset($settings['category-mode']) ? $settings['category-mode'] : '') . "\r\n";
    }
    $cabrillo .= "CATEGORY-POWER: " . (isset($settings['category-power']) ? $settings['category-power'] : '') . "\r\n";
    $cabrillo .= "CATEGORY-STATION: " . (isset($settings['category-station']) ? $settings['category-station'] : 'FIXED') . "\r\n";
    $cabrillo .= "CATEGORY-TRANSMITTER: " . (isset($settings['category-transmitter']) ? $settings['category-transmitter'] : '') . "\r\n";
    $cabrillo .= "CLAIMED-SCORE: " . $score . "\r\n";
    $cabrillo .= "NAME: " . (isset($settings['name']) ? $settings['name'] : '') . "\r\n";
    $cabrillo .= "ADDRESS: " . (isset($settings['address']) ? $settings['address'] : '') . "\r\n";
    $cabrillo .= "ADDRESS-CITY: " . (isset($settings['city']) ? $settings['city'] : '') . "\r\n";
    $cabrillo .= "ADDRESS-STATE-PROVINCE: " . (isset($settings['state']) ? $settings['state'] : '') . "\r\n";
    $cabrillo .= "ADDRESS-POSTALCODE: " . (isset($settings['postal']) ? $settings['postal'] : '') . "\r\n";
    $cabrillo .= "ADDRESS-COUNTRY: " . (isset($settings['country']) ? $settings['country'] : '') . "\r\n";

    $cabrillo .= "CREATED-BY: QRUQSP.org 13ColoniesLogger2020\r\n";

    $cabrillo .= $cabrillo_qsos;
    $cabrillo .= "END-OF-LOG:\r\n";

    header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT', true, 200);
    header("Content-type: text/plain");
    header('Content-Disposition: attachment; filename="13coloniescontest2020.log"');

    print $cabrillo;
    
    return array('stat'=>'exit');
}
?>
