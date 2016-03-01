<?php

global $itlp_settings;

// General Settings section
$itlp_settings[] = array(
    'section_id' => 'general',
    'section_title' => 'General Settings',
    'section_description' => '',
    'section_order' => 5,
    'fields' => array(
        array(
            'id' => 'select',
            'title' => 'Track Post Status',
            'desc' => 'Should be set to draft',
            'type' => 'select',
            'std' => 'draft',
            'choices' => array(
                'draft' => 'Draft',
                'publish' => 'Publish'
            )
        ),
        array(
            'id' => 'lm_login',
            'title' => 'Require Users to login (recommended)',
            'desc' => '',
            'type' => 'checkbox',
            'std' => 1
        ),
        array(
            'id' => 'lm_userroles',
            'title' => 'User Roles',
            'desc' => 'Further limit who is allowed to submit tracks.',
            'type' => 'checkboxes',
            'std' => array('false'),
            'choices' => array(
                'activate_plugins' => 'Administrator',
                'moderate_comments' => 'Editor',
                'edit_published_posts' => 'Author',
                'edit_posts' => 'Contributor',
                'read' => 'Subscriber',
                'false' => 'All have permission'                
            )
        ),
        array(
            'id' => 'lm_pagination_top',
            'title' => 'Include Pagination on top (recommended)',
            'desc' => '',
            'type' => 'checkbox',
            'std' => 1
        ),
        array(
            'id' => 'lm_pagination_bottom',
            'title' => 'Include Pagination on bottom (recommended)',
            'desc' => '',
            'type' => 'checkbox',
            'std' => 1
        ),
        array(
            'id' => 'lm_display_rating',
            'title' => 'Display Rating (recommended)',
            'desc' => '',
            'type' => 'checkbox',
            'std' => 1
        ),
    )
);
$itlp_settings[] = array(
    'section_id' => 'styling',
    'section_title' => 'Styling',
    'section_description' => '',
    'section_order' => 6,
    'fields' => array(  
        array(
            'id' => 'lm_boxbg',
            'title' => 'Item Box Backgound Color',
            'desc' => '(optional)',
            'type' => 'color',
            'std' => '#ffffff'
        ),
        array(
            'id' => 'lm_boxborder',
            'title' => 'Item Border Color',
            'desc' => '(optional)',
            'type' => 'color',
            'std' => 'rgb(255, 255, 255)'
        ),
        array(
            'id' => 'lm_boxbar',
            'title' => 'Track Backgound Color',
            'desc' => '(optional)',
            'type' => 'color',
            'std' => 'rgb(192, 192, 192)'
        ),
        array(
            'id' => 'lm_selectbg',
            'title' => 'Instant Search Select Backgound Color',
            'desc' => '(optional)',
            'type' => 'color',
            'std' => '#ffffff'
        ),
        array(
            'id' => 'lm_selectcolor',
            'title' => 'Instant Search Select Color',
            'desc' => '(optional)',
            'type' => 'color',
            'std' => '#666666'
        ),
        array(
            'id' => 'lm_selectnotice',
            'title' => 'Select Notice Backgound Color',
            'desc' => '(optional)',
            'type' => 'color',
            'std' => '#F7F7F7'
        ),
        array(
            'id' => 'lm_paginationcolor',
            'title' => 'Pagination Color',
            'desc' => '(optional)',
            'type' => 'color',
            'std' => '#0f3647'
        ),
        array(
            'id' => 'lm_paginationcolorhover',
            'title' => 'Pagination Hover Color',
            'desc' => '(optional)',
            'type' => 'color',
            'std' => '#FF6E29'
        ),
        array(
            'id' => 'lm_boxwidth',
            'title' => 'Box Width (%)',
            'desc' => '',
            'type' => 'range',
            'std' => '42',
            'min' => '10',
            'max'  => '49'
        ),
        array(
            'id' => 'lm_boxheight',
            'title' => 'Box Height (px)',
            'desc' => '',
            'type' => 'range',
            'std' => '300',
            'min' => '10',
            'max'  => '400'
        ),
    )
);



$itlp_settings[] = array(
    'section_id' => 'aff',
    'section_title' => 'Affiliate',
    'section_description' => '',
    'section_order' => 8,
    'fields' => array(
        array(
            'id' => 'lm_affiliate',
            'title' => 'Use iTunes Affiliate Links',
            'desc' => '',
            'type' => 'checkbox',
            'std' => 0
        ),
        array(
            'id' => 'lm_linkselect',
            'title' => 'Affiliate Link System',
            'desc' => 'e.g. LinkShare/TradeDoubler/DMG',
            'type' => 'select',
            'std' => '',
            'choices' => array(
                'siteID' => 'LinkShare',
                'tduid' => 'TradeDoubler',
                'affToken' =>'DMG'
            )
        ),
        array(
            'id' => 'lm_partnerid',
            'title' => 'Enter your Partner ID',
            'desc' => '<a href="http://www.apple.com/itunes/affiliates/resources/documentation/linking-to-the-itunes-music-store.html#apps"> Get one </a>',
            'type' => 'text',
            'std' => ''
        ),
        array(
            'id' => 'lm_afftoken',
            'title' => 'Affiliate Token',
            'desc' => '<a href="http://www.apple.com/itunes/affiliates/resources/documentation/linking-to-the-itunes-music-store.html#apps"> Lern more </a>',
            'type' => 'text',
            'std' => ''
        ),
    )
);
?>