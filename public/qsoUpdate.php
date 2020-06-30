<?php
//
// Description
// ===========
//
// Arguments
// ---------
//
// Returns
// -------
//
function qruqsp_13colonieslog_qsoUpdate(&$ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'),
        'qso_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'QSO'),
        'qso_dt'=>array('required'=>'no', 'blank'=>'no', 'type'=>'datetime', 'name'=>'UTC Date Time of QSO'),
        'callsign'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Call Sign'),
        'recv_rst'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Received RS(T)'),
        'recv_state_country'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Received Province Serial'),
        'sent_rst'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Sent RS(T)'),
        'band'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Band'),
        'mode'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Mode'),
        'frequency'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Frequency'),
        'operator'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Operator'),
        'flags'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Options'),
        'notes'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Notes'),
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
    $rc = qruqsp_13colonieslog_checkAccess($ciniki, $args['tnid'], 'qruqsp.13colonieslog.qsoUpdate');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Uppercase the fields
    //
    if( isset($args['callsign']) ) {
        $args['callsign'] = strtoupper($args['callsign']);
    }
    if( isset($args['recv_state_country']) ) {
        $args['recv_state_country'] = trim(strtoupper($args['recv_state_country']));
    }
    if( isset($args['mode']) && !in_array($args['mode'], array('CW', 'PH')) ) {
        return array('stat'=>'warn', 'err'=>array('code'=>'qruqsp.13colonieslog.28', 'msg'=>'Please choose a mode'));
    }

    //
    // Load existing qso
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
        . "WHERE qruqsp_13colonieslog_qsos.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "AND qruqsp_13colonieslog_qsos.id = '" . ciniki_core_dbQuote($ciniki, $args['qso_id']) . "' "
        . "";
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'qruqsp.13colonieslog', 'qso');
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'qruqsp.13colonieslog.30', 'msg'=>'Unable to load contact', 'err'=>$rc['err']));
    }
    if( !isset($rc['qso']) ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'qruqsp.13colonieslog.31', 'msg'=>'Unable to find requested contact'));
    }
    $qso = $rc['qso'];
    
    //
    // Load the settings
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbDetailsQuery');
    $rc = ciniki_core_dbDetailsQuery($ciniki, 'qruqsp_13colonieslog_settings', 'tnid', $args['tnid'], 'qruqsp.13colonieslog', 'settings', '');
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'qruqsp.13colonieslog.19', 'msg'=>'Unable to load settings', 'err'=>$rc['err']));
    }
    $settings = isset($rc['settings']) ? $rc['settings'] : array();

    //
    // Check if allow-dupes is set and no
    //
    if( isset($settings['allow-dupes']) && $settings['allow-dupes'] == 'no' ) {
        //
        // Check for dupe
        //
        ciniki_core_loadMethod($ciniki, 'qruqsp', '13colonieslog', 'private', 'checkDupe');
        $rc = qruqsp_13colonieslog_checkDupe($ciniki, $args['tnid'], array(
            'id' => $args['qso_id'],
            'callsign' => (isset($args['callsign']) ? $args['callsign'] : $qso['callsign']),
            'band' => (isset($args['band']) ? $args['band'] : $qso['band']),
            'mode' => (isset($args['mode']) ? $args['mode'] : $qso['mode']),
            ));
        if( $rc['stat'] != 'ok' ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'qruqsp.13colonieslog.29', 'msg'=>'Unable to check for dupe', 'err'=>$rc['err']));
        }
        if( isset($rc['dupe']) && $rc['dupe'] == 'yes' ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'qruqsp.13colonieslog.30', 'msg'=>'Duplicate contact.'));
        }
    }

    //
    // Start transaction
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionStart');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionRollback');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionCommit');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbAddModuleHistory');
    $rc = ciniki_core_dbTransactionStart($ciniki, 'qruqsp.13colonieslog');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Update the QSO in the database
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectUpdate');
    $rc = ciniki_core_objectUpdate($ciniki, $args['tnid'], 'qruqsp.13colonieslog.qso', $args['qso_id'], $args, 0x04);
    if( $rc['stat'] != 'ok' ) {
        ciniki_core_dbTransactionRollback($ciniki, 'qruqsp.13colonieslog');
        return $rc;
    }

    //
    // Commit the transaction
    //
    $rc = ciniki_core_dbTransactionCommit($ciniki, 'qruqsp.13colonieslog');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Update the last_change date in the tenant modules
    // Ignore the result, as we don't want to stop user updates if this fails.
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'tenants', 'private', 'updateModuleChangeDate');
    ciniki_tenants_updateModuleChangeDate($ciniki, $args['tnid'], 'qruqsp', '13colonieslog');

    //
    // Update the web index if enabled
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'hookExec');
    ciniki_core_hookExec($ciniki, $args['tnid'], 'ciniki', 'web', 'indexObject', array('object'=>'qruqsp.13colonieslog.qso', 'object_id'=>$args['qso_id']));

    return array('stat'=>'ok');
}
?>
