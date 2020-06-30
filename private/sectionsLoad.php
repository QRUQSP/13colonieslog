<?php
//
// Description
// -----------
// This function will return the list of areas and sections
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
function qruqsp_13colonieslog_sectionsLoad($ciniki) {

    $areas = array(
        'DX' => array(
            'name' => 'DX', 
            'sections' => array(
                array('label' => 'DX', 'name' => 'DX', 'bit' => 0),
            ),
        ),
        '_1' => array(
            'name' => '1', 
            'sections' => array(
                array('label' => 'CT', 'name' => 'Connecticut', 'bit' => 1),
                array('label' => 'EMA', 'name' => 'Eastern Massachusetts', 'bit' => 2),
                array('label' => 'ME', 'name' => 'Maine', 'bit' => 3),
                array('label' => 'NH', 'name' => 'New Hampshire', 'bit' => 4),
                array('label' => 'RI', 'name' => 'Rhode Island', 'bit' => 5),
                array('label' => 'VT', 'name' => 'Vermont', 'bit' => 6),
                array('label' => 'WMA', 'name' => 'Western Massachusetts', 'bit' => 7),
            ),
        ),
        '_2' => array(
            'name' => '2', 
            'sections' => array(
                array('label' => 'ENY', 'name' => 'Eastern New York', 'bit' => 8),
                array('label' => 'NLI', 'name' => 'NYC/Long Island', 'bit' => 9),
                array('label' => 'NNJ', 'name' => 'Northern New Jersey', 'bit' => 10),
                array('label' => 'NNY', 'name' => 'Northern New York', 'bit' => 11),
                array('label' => 'SNJ', 'name' => 'Southern New Jersey', 'bit' => 12),
                array('label' => 'WNY', 'name' => 'Western New York', 'bit' => 13),
            ),
        ),
        '_3' => array(
            'name' => '3', 
            'sections' => array(
                array('label' => 'DE', 'name' => 'Delaware', 'bit' => 14),
                array('label' => 'EPA', 'name' => 'Eastern Pennsylvania', 'bit' => 15),
                array('label' => 'MDC', 'name' => 'Maryland - DC', 'bit' => 16),
                array('label' => 'WPA', 'name' => 'Western Pennsylvania', 'bit' => 17),
            ),
        ),
        '_4' => array(
            'name' => '4', 
            'sections' => array(
                array('label' => 'AL', 'name' => 'Alabama', 'bit' => 18),
                array('label' => 'GA', 'name' => 'Georgia', 'bit' => 19),
                array('label' => 'KY', 'name' => 'Kentucky', 'bit' => 20),
                array('label' => 'NC', 'name' => 'North Carolina', 'bit' => 21),
                array('label' => 'NFL', 'name' => 'Northern Florida', 'bit' => 22),
                array('label' => 'SC', 'name' => 'South Carolina', 'bit' => 23),
                array('label' => 'SFL', 'name' => 'Southern Florida', 'bit' => 24),
                array('label' => 'TN', 'name' => 'Tennessee', 'bit' => 25),
                array('label' => 'VA', 'name' => 'Virginia', 'bit' => 26),
                array('label' => 'WCF', 'name' => 'West Central Florida', 'bit' => 27),
                array('label' => 'PR', 'name' => 'Puerto Rico', 'bit' => 28),
                array('label' => 'VI', 'name' => 'US Virgin Islands', 'bit' => 29),
            ),
        ),
        '_5' => array(
            'name' => '5', 
            'sections' => array(
                array('label' => 'AR', 'name' => 'Arkansas', 'bit' => 30),
                array('label' => 'LA', 'name' => 'Louisiana', 'bit' => 31),
                array('label' => 'MS', 'name' => 'Mississippi', 'bit' => 32),
                array('label' => 'NM', 'name' => 'New Mexico', 'bit' => 33),
                array('label' => 'NTX', 'name' => 'North Texas', 'bit' => 34),
                array('label' => 'OK', 'name' => 'Oklahoma', 'bit' => 35),
                array('label' => 'STX', 'name' => 'South Texas', 'bit' => 36),
                array('label' => 'WTX', 'name' => 'West Texas', 'bit' => 37),
            ),
        ),
        '_6' => array(
            'name' => '6', 
            'sections' => array(
                array('label' => 'EB', 'name' => 'East Bay', 'bit' => 38),
                array('label' => 'LAX', 'name' => 'Los Angeles', 'bit' => 39),
                array('label' => 'ORG', 'name' => 'Orange', 'bit' => 40),
                array('label' => 'SB', 'name' => 'Santa Barbara', 'bit' => 41),
                array('label' => 'SCV', 'name' => 'Santa Clara Valley', 'bit' => 42),
                array('label' => 'SDG', 'name' => 'San Diego', 'bit' => 43),
                array('label' => 'SF', 'name' => 'San Francisco', 'bit' => 44),
                array('label' => 'SJV', 'name' => 'San Joaquin Valley', 'bit' => 45),
                array('label' => 'SV', 'name' => 'Sacramento Valley', 'bit' => 46),
                array('label' => 'PAC', 'name' => 'Pacific', 'bit' => 47),
            ),
        ),
        '_7' => array(
            'name' => '7', 
            'sections' => array(
                array('label' => 'AK', 'name' => 'Alaska', 'bit' => 48),
                array('label' => 'AZ', 'name' => 'Arizona', 'bit' => 49),
                array('label' => 'EWA', 'name' => 'Eastern Washington', 'bit' => 50),
                array('label' => 'ID', 'name' => 'Idaho', 'bit' => 51),
                array('label' => 'MT', 'name' => 'Montana', 'bit' => 52),
                array('label' => 'NV', 'name' => 'Nevada', 'bit' => 53),
                array('label' => 'OR', 'name' => 'Oregon', 'bit' => 54),
                array('label' => 'UT', 'name' => 'Utah', 'bit' => 55),
                array('label' => 'WWA', 'name' => 'Western Washington', 'bit' => 56),
                array('label' => 'WY', 'name' => 'Wyoming', 'bit' => 57),
            ),
        ),
        '_8' => array(
            'name' => '8', 
            'sections' => array(
                array('label' => 'MI', 'name' => 'Michigan', 'bit' => 58),
                array('label' => 'OH', 'name' => 'Ohio', 'bit' => 59),
                array('label' => 'WV', 'name' => 'West Virginia', 'bit' => 60),
            ),
        ),
        '_9' => array(
            'name' => '9', 
            'sections' => array(
                array('label' => 'IL', 'name' => 'Illinois', 'bit' => 61),
                array('label' => 'IN', 'name' => 'Indiana', 'bit' => 62),
                array('label' => 'WI', 'name' => 'Wisconsin', 'bit' => 63),
            ),
        ),
        '_0' => array(
            'name' => '0', 
            'sections' => array(
                array('label' => 'CO', 'name' => 'Colorado', 'bit' => 64),
                array('label' => 'IA', 'name' => 'Iowa', 'bit' => 65),
                array('label' => 'KS', 'name' => 'Kansas', 'bit' => 66),
                array('label' => 'MN', 'name' => 'Minnesota', 'bit' => 67),
                array('label' => 'MO', 'name' => 'Missouri', 'bit' => 68),
                array('label' => 'NE', 'name' => 'Nebraska', 'bit' => 69),
                array('label' => 'ND', 'name' => 'North Dakota', 'bit' => 70),
                array('label' => 'SD', 'name' => 'South Dakota', 'bit' => 71),
            ),
        ),
        'CANADA' => array(
            'name' => 'CA', 
            'sections' => array(
                array('label' => 'AB', 'name' => 'Alberta', 'bit' => 72),
                array('label' => 'BC', 'name' => 'British Columbia', 'bit' => 73),
                array('label' => 'GTA', 'name' => 'Greater Toronto Area', 'bit' => 74),
                array('label' => 'MAR', 'name' => 'Maritime', 'bit' => 75),
                array('label' => 'MB', 'name' => 'Manitoba', 'bit' => 76),
                array('label' => 'NL', 'name' => 'Newfoundland/Labrador', 'bit' => 77),
                array('label' => 'NT', 'name' => 'Northern Territories', 'bit' => 78),
                array('label' => 'ONE', 'name' => 'Ontario East', 'bit' => 79),
                array('label' => 'ONN', 'name' => 'Ontario North', 'bit' => 80),
                array('label' => 'ONS', 'name' => 'Ontario South', 'bit' => 81),
                array('label' => 'PE', 'name' => 'Prince Edward Island', 'bit' => 82),
                array('label' => 'QC', 'name' => 'Quebec', 'bit' => 83),
                array('label' => 'SK', 'name' => 'Saskatchewan', 'bit' => 84),
            ),
        ),
    );

    $sections = array();
    foreach($areas as $aid => $area) {
        foreach($area['sections'] as $sid => $section) {
            $sections[$section['label']] = array(
                'areas' => $aid,
                'name' => $section['name'],
                'bit' => $section['bit'],
                'num_qsos' => 0,
                );
        }
    }

    return array('stat'=>'ok', 'areas'=>$areas, 'sections'=>$sections);
}
?>
