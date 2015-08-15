<?php if ( ! defined( 'ABSPATH' ) ) exit;

date_default_timezone_set('America/New_York');

final class KBJ_SportsPress_API
{
    const TEXTDOMAIN = 'kbj-sportspress-api';

    private static $instance;

    public $with = array();

    public $today = FALSE;

    public static function instance() {
        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof KBJ_SportsPress_API ) ) {
            self::$instance = new KBJ_SportsPress_API;

        }

        return self::$instance;
    }

    /*
     * Today
     */
    public function today()
    {
        $this->today = TRUE;
        return $this;
    }

    /*
     * With
     */
    public function with( $with )
    {
        if( is_array( $with ) ){

            foreach( $with as $w ){
                $this->with[ $w ] = $w;
            }

        } else {
            $this->with[ $with ] = $with;
        }

        return $this;
    }

    /*
     * Get Events by Venue
     */
    public function get_events_by_venue( $venue_id )
    {
        $args = array(
            'post_type' => 'sp_event',
            'tax_query' => array(
                array(
                    'taxonomy' => 'sp_venue',
                    'field' => 'id',
                    'terms' => $venue_id
                )
            )
        );
        $events = get_posts( $args );

        // Assign event IDs as array keys
        foreach( $events as $key => $event ){
            $events[ $event->ID ] = $event;
            unset( $events[ $key ] );
        }

        // "today" check
        if( $this->today && is_array( $events ) ) {

            foreach( $events as $key => $event ) {

                if( date('Ymd', strtotime( $event->post_date )) !== date('Ymd', time() ) ){
                    unset( $events[ $key ] );
                }
            }
        }

        // "with teams" check
        if( isset( $this->with['teams'] ) && is_array( $events ) ){

            foreach( $events as $key => $event ) {
                $events[ $key ]->teams = $this->get_teams_by_event( $event->ID );
            }
        }

        // "with results" check
        if( isset( $this->with['results'] ) && is_array( $events ) ) {

            foreach( $events as $key => $event ) {
                $results = maybe_unserialize(get_post_meta($event->ID, 'sp_results', TRUE));
                $event->results = $results;
            }
        }

        return ( is_array( $events ) ) ? $events : FALSE;
    }

    /*
     * Get Teams by Event
     */
    public function get_teams_by_event( $event_id )
    {
        $event_teams = get_post_meta( $event_id, 'sp_team' );

        $teams = array();
        foreach( $event_teams as $event_team_id ){

            $team = get_post( $event_team_id );

            if( ! is_object( $team ) ) return FALSE;

            // "with results" check
            if( isset( $this->with['results'] ) && is_array( $teams ) ) {

                $results = maybe_unserialize( get_post_meta( $event_id, 'sp_results', TRUE) );
                $team->result = $results[ $team->ID ];
            }

            $team->logo = $this->get_team_logo( $team->ID );

            $teams[ $team->ID ] = $team;
        }

        return $teams;
    }

    /*
     * Get Team Logo
     */
    public function get_team_logo( $team_id )
    {
        $featured_image = wp_get_attachment_image_src( get_post_thumbnail_id( $team_id ), 'thumbnail' );
        return $featured_image[0];
    }

    public function update_event_results( $event_id, $results ){
        update_post_meta( $event_id, 'sp_results', $results );
    }


}

function SP_API() {
    return KBJ_SportsPress_API::instance();
}
