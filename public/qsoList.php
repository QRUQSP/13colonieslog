<?php
//
// Description
// -----------
// This method will return the list of QSOs for a tenant.
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
function qruqsp_13colonieslog_qsoList($ciniki) {
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
        return array('stat'=>'fail', 'err'=>array('code'=>'qruqsp.13colonieslog.17', 'msg'=>'', 'err'=>$rc['err']));
    }
    $settings = isset($rc['settings']) ? $rc['settings'] : array();

    //
    // Get the list of qsos
    //
    $strsql = "SELECT qruqsp_13colonieslog_qsos.id, "
        . "qruqsp_13colonieslog_qsos.qso_dt, "
        . "DATE_FORMAT(qruqsp_13colonieslog_qsos.qso_dt, '%b %d %H:%i') AS qso_dt_display, "
        . "qruqsp_13colonieslog_qsos.callsign, "
        . "qruqsp_13colonieslog_qsos.recv_rst, "
        . "qruqsp_13colonieslog_qsos.recv_state_country, "
        . "qruqsp_13colonieslog_qsos.sent_rst, "
        . "qruqsp_13colonieslog_qsos.band, "
        . "qruqsp_13colonieslog_qsos.mode, "
        . "qruqsp_13colonieslog_qsos.frequency, "
        . "qruqsp_13colonieslog_qsos.flags, "
        . "qruqsp_13colonieslog_qsos.operator, "
        . "qruqsp_13colonieslog_qsos.notes "
        . "FROM qruqsp_13colonieslog_qsos "
        . "WHERE qruqsp_13colonieslog_qsos.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "ORDER BY qso_dt DESC "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
    $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'qruqsp.13colonieslog', array(
        array('container'=>'qsos', 'fname'=>'id', 
            'fields'=>array('id', 'qso_dt', 'qso_dt_display', 'callsign', 
                'recv_rst', 'recv_state_country', 'sent_rst', 
                'band', 'mode', 'frequency', 'flags', 'operator', 'notes',
                )),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( isset($rc['qsos']) ) {
        $qsos = $rc['qsos'];
        $qso_ids = array();
        foreach($qsos as $iid => $qso) {
            $qso_ids[] = $qso['id'];
        }
    } else {
        $qsos = array();
        $qso_ids = array();
    }

    return array('stat'=>'ok', 'qsos'=>$qsos, 'nplist'=>$qso_ids, 'settings'=>$settings);
}
?>
