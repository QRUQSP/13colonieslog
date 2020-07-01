<?php
//
// Description
// -----------
// This method will create an excel file with all qso details
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
function qruqsp_13colonieslog_exportExcel($ciniki) {
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
        . "IF((qruqsp_13colonieslog_qsos.flags&0x01) = 0x01, 'Yes', 'No') AS gota, "
        . "qruqsp_13colonieslog_qsos.operator, "
        . "qruqsp_13colonieslog_qsos.notes "
        . "FROM qruqsp_13colonieslog_qsos "
        . "WHERE qruqsp_13colonieslog_qsos.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "AND YEAR(qso_dt) = 2020 "
        . "ORDER BY qso_dt ASC "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
    $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'qruqsp.13colonieslog', array(
        array('container'=>'qsos', 'fname'=>'id', 
            'fields'=>array('id', 'qso_dt', 'qso_dt_display', 'callsign', 'recv_rst', 'recv_state_country', 'sent_rst', 'band', 'mode', 'frequency', 
                'gota', 'operator', 'notes'),
            ),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $qsos = isset($rc['qsos']) ? $rc['qsos'] : array();

    //
    // Create excel file
    //
    require($ciniki['config']['core']['lib_dir'] . '/PHPExcel/PHPExcel.php');
    $objPHPExcel = new PHPExcel();
    $objPHPExcelWorksheet = $objPHPExcel->setActiveSheetIndex(0);

    $columns = array(
        'qso_dt_display' => 'Date/Time',
        'callsign' => 'Call Sign',
        'class' => 'Class',
        'section' => 'Section',
        'band' => 'Band',
        'mode' => 'Mode',
        );
    if( isset($settings['category-operator']) && $settings['category-operator'] == 'MULTI-OP' ) {
        $columns['gota'] = 'GOTA';
        $columns['operator'] = 'Operator';
    }
    if( isset($settings['ui-notes']) && $settings['ui-notes'] == 'yes' ) {
        $columns['notes'] = 'Notes';
    }
    $row = 1;
    $col = 0;
    foreach($columns as $k => $v) {
        $objPHPExcelWorksheet->setCellValueByColumnAndRow($col++, $row, $v, false);
    }
    $objPHPExcelWorksheet->getStyle('A1:' . PHPExcel_Cell::stringFromColumnIndex($col) . '1')->getFont()->setBold(true);
    $objPHPExcelWorksheet->freezePane('A2');

    $row++;

    foreach($qsos as $qso) {
        $col = 0;
        foreach($columns as $k => $v) {
            $objPHPExcelWorksheet->setCellValueByColumnAndRow($col, $row, $qso[$k], false);
            $col++;
        }
        $row++;
    }

    PHPExcel_Shared_Font::setAutoSizeMethod(PHPExcel_Shared_Font::AUTOSIZE_METHOD_EXACT);

    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="13ColoniesContestContacts.xls"');
    header('Cache-Control: max-age=0');

    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
    $objWriter->save('php://output');

    return array('stat'=>'exit');
}
?>
