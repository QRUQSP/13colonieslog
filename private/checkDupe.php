<?php
//
// Description
// -----------
// Check current qsos for matching callsign, band and mode.
// 
// Arguments
// ---------
// ciniki: 
// tnid:            The ID of the current tenant.
// 
// Returns
// ---------
// 
function qruqsp_13colonieslog_checkDupe(&$ciniki, $tnid, $args) {

    //
    // Check for existing qso
    //
    $strsql = "SELECT qruqsp_13colonieslog_qsos.id, "
        . "qruqsp_13colonieslog_qsos.qso_dt, "
        . "qruqsp_13colonieslog_qsos.callsign, "
        . "qruqsp_13colonieslog_qsos.recv_rst, "
        . "qruqsp_13colonieslog_qsos.recv_state_country, "
        . "qruqsp_13colonieslog_qsos.sent_rst, "
        . "qruqsp_13colonieslog_qsos.band, "
        . "qruqsp_13colonieslog_qsos.mode, "
        . "qruqsp_13colonieslog_qsos.frequency, "
        . "qruqsp_13colonieslog_qsos.operator, "
        . "qruqsp_13colonieslog_qsos.notes "
        . "FROM qruqsp_13colonieslog_qsos "
        . "WHERE qruqsp_13colonieslog_qsos.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
        . "AND qruqsp_13colonieslog_qsos.callsign = '" . ciniki_core_dbQuote($ciniki, $args['callsign']) . "' "
        . "AND qruqsp_13colonieslog_qsos.band = '" . ciniki_core_dbQuote($ciniki, $args['band']) . "' "
        . "AND qruqsp_13colonieslog_qsos.mode = '" . ciniki_core_dbQuote($ciniki, $args['mode']) . "' "
        . "";
    if( isset($args['id']) && $args['id'] != '' ) {
        $strsql .= "AND qruqsp_13colonieslog_qsos.id <> '" . ciniki_core_dbQuote($ciniki, $args['id']) . "' ";
    }
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'qruqsp.13colonieslog', 'qso');
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'qruqsp.13colonieslog.30', 'msg'=>'Unable to load contact', 'err'=>$rc['err']));
    }
    if( isset($rc['rows']) && count($rc['rows']) > 0 ) {
        return array('stat'=>'ok', 'dupe'=>'yes');
    }

    return array('stat'=>'ok', 'dupe'=>'no');
}
?>
