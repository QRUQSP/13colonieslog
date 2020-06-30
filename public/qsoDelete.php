<?php
//
// Description
// -----------
// This method will delete an qso.
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:            The ID of the tenant the qso is attached to.
// qso_id:            The ID of the qso to be removed.
//
// Returns
// -------
//
function qruqsp_13colonieslog_qsoDelete(&$ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'),
        'qso_id'=>array('required'=>'yes', 'blank'=>'yes', 'name'=>'QSO'),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args = $rc['args'];

    //
    // Check access to tnid as owner
    //
    ciniki_core_loadMethod($ciniki, 'qruqsp', '13colonieslog', 'private', 'checkAccess');
    $rc = qruqsp_13colonieslog_checkAccess($ciniki, $args['tnid'], 'ciniki.13colonieslog.qsoDelete');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Get the current settings for the qso
    //
    $strsql = "SELECT id, uuid "
        . "FROM qruqsp_13colonieslog_qsos "
        . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "AND id = '" . ciniki_core_dbQuote($ciniki, $args['qso_id']) . "' "
        . "";
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.13colonieslog', 'qso');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( !isset($rc['qso']) ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'qruqsp.13colonieslog.4', 'msg'=>'QSO does not exist.'));
    }
    $qso = $rc['qso'];

    //
    // Check for any dependencies before deleting
    //

    //
    // Check if any modules are currently using this object
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectCheckUsed');
    $rc = ciniki_core_objectCheckUsed($ciniki, $args['tnid'], 'qruqsp.13colonieslog.qso', $args['qso_id']);
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'qruqsp.13colonieslog.14', 'msg'=>'Unable to check if the qso is still being used.', 'err'=>$rc['err']));
    }
    if( $rc['used'] != 'no' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'qruqsp.13colonieslog.6', 'msg'=>'The qso is still in use. ' . $rc['msg']));
    }

    //
    // Start transaction
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionStart');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionRollback');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionCommit');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbDelete');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectDelete');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbAddModuleHistory');
    $rc = ciniki_core_dbTransactionStart($ciniki, 'qruqsp.13colonieslog');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Remove the qso
    //
    $rc = ciniki_core_objectDelete($ciniki, $args['tnid'], 'qruqsp.13colonieslog.qso',
        $args['qso_id'], $qso['uuid'], 0x04);
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

    return array('stat'=>'ok');
}
?>
