<?php
/**
 * Anime airing calendar.
 *
 * Fetches the upcoming 7-day anime airing schedule and stores it in the DB
 * (wp_options: 'amu_anime_schedule'), refreshed by a daily WP-Cron event and,
 * as a fallback, whenever the stored copy is older than 24h on page view.
 *
 * Source: AniList GraphQL (free, no token). animeschedule.net needs an API
 * token — plug one in via the 'amu_schedule_provider' filter to switch source.
 *
 * @package amu
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const AMU_SCHED_OPT = 'amu_anime_schedule';

/** Fetch + normalize the next 7 days of airing anime; store in the DB. Returns items. */
function amu_fetch_anime_schedule() {
	$start = time();
	$end   = $start + 8 * DAY_IN_SECONDS;
	$query = 'query($s:Int,$e:Int,$p:Int){Page(page:$p,perPage:50){pageInfo{hasNextPage}airingSchedules(airingAt_greater:$s,airingAt_lesser:$e,sort:TIME){airingAt episode media{title{romaji english}coverImage{medium color}siteUrl format isAdult}}}}';
	$items = array();
	$page  = 1;
	do {
		$resp = wp_remote_post( 'https://graphql.anilist.co', array(
			'timeout' => 20,
			'headers' => array( 'Content-Type' => 'application/json', 'Accept' => 'application/json' ),
			'body'    => wp_json_encode( array( 'query' => $query, 'variables' => array( 's' => $start, 'e' => $end, 'p' => $page ) ) ),
		) );
		if ( is_wp_error( $resp ) ) {
			break;
		}
		$pg = json_decode( wp_remote_retrieve_body( $resp ), true )['data']['Page'] ?? null;
		if ( ! $pg ) {
			break;
		}
		foreach ( (array) ( $pg['airingSchedules'] ?? array() ) as $s ) {
			$m = $s['media'] ?? array();
			if ( ! empty( $m['isAdult'] ) ) {
				continue;
			}
			$title = ! empty( $m['title']['english'] ) ? $m['title']['english'] : ( $m['title']['romaji'] ?? '' );
			if ( '' === $title ) {
				continue;
			}
			$items[] = array(
				't'     => (int) $s['airingAt'],
				'ep'    => (int) $s['episode'],
				'title' => $title,
				'img'   => $m['coverImage']['medium'] ?? '',
				'url'   => $m['siteUrl'] ?? '',
				'fmt'   => $m['format'] ?? '',
			);
		}
		$more = ! empty( $pg['pageInfo']['hasNextPage'] );
		$page++;
	} while ( $more && $page <= 8 );

	$items = apply_filters( 'amu_schedule_provider', $items ); // swap in animeschedule.net here if a token is added.
	if ( $items ) {
		usort( $items, function ( $a, $b ) { return $a['t'] <=> $b['t']; } );
		update_option( AMU_SCHED_OPT, array( 'updated' => time(), 'items' => $items ), false );
	}
	return $items;
}

/** Read the stored schedule, refreshing when empty or older than 24h. */
function amu_get_anime_schedule() {
	$data = get_option( AMU_SCHED_OPT );
	if ( ! $data || empty( $data['items'] ) || ( time() - (int) $data['updated'] ) > DAY_IN_SECONDS ) {
		amu_fetch_anime_schedule();
		$data = get_option( AMU_SCHED_OPT );
	}
	return $data ?: array( 'updated' => 0, 'items' => array() );
}

/* Daily refresh via WP-Cron. */
add_action( 'amu_refresh_schedule', 'amu_fetch_anime_schedule' );
add_action( 'after_setup_theme', function () {
	if ( ! wp_next_scheduled( 'amu_refresh_schedule' ) ) {
		wp_schedule_event( time() + 60, 'daily', 'amu_refresh_schedule' );
	}
} );

/** Render the responsive 7-day calendar grid. */
function amu_render_calendar() {
	$data  = amu_get_anime_schedule();
	$items = $data['items'];

	// Bucket into today..+6 days, keyed by local date (site timezone via wp_date).
	$buckets = array();
	$order   = array();
	for ( $d = 0; $d < 7; $d++ ) {
		$ts  = time() + $d * DAY_IN_SECONDS;
		$key = wp_date( 'Y-m-d', $ts );
		$buckets[ $key ] = array( 'ts' => $ts, 'today' => 0 === $d, 'items' => array() );
		$order[] = $key;
	}
	foreach ( $items as $it ) {
		$key = wp_date( 'Y-m-d', $it['t'] );
		if ( isset( $buckets[ $key ] ) ) {
			$buckets[ $key ]['items'][] = $it;
		}
	}

	echo '<div class="cal-grid">';
	foreach ( $order as $key ) {
		$day = $buckets[ $key ];
		printf(
			'<div class="cal-day%s"><div class="cal-dayhead"><span class="cal-dow">%s</span><span class="cal-date">%s</span></div><div class="cal-slots">',
			$day['today'] ? ' -today' : '',
			esc_html( wp_date( 'D', $day['ts'] ) ),
			esc_html( wp_date( 'M j', $day['ts'] ) )
		);
		if ( empty( $day['items'] ) ) {
			echo '<p class="cal-empty">' . esc_html__( 'Nothing scheduled', 'amu' ) . '</p>';
		}
		foreach ( $day['items'] as $it ) {
			printf(
				'<a class="cal-item" href="%s" target="_blank" rel="noopener nofollow"><span class="cal-time">%s</span>%s<span class="cal-info"><span class="cal-title">%s</span><span class="cal-ep">%s</span></span></a>',
				esc_url( $it['url'] ),
				esc_html( wp_date( 'H:i', $it['t'] ) ),
				$it['img'] ? '<img class="cal-thumb" src="' . esc_url( $it['img'] ) . '" loading="lazy" alt="" width="34" height="46">' : '',
				esc_html( $it['title'] ),
				$it['ep'] ? esc_html( sprintf( __( 'Ep %d', 'amu' ), $it['ep'] ) ) : ''
			);
		}
		echo '</div></div>';
	}
	echo '</div>';
	if ( $data['updated'] ) {
		printf( '<p class="cal-updated">%s</p>', esc_html( sprintf( __( 'Auto-updated %s · data from AniList', 'amu' ), wp_date( 'M j, H:i', $data['updated'] ) ) ) );
	}
}
