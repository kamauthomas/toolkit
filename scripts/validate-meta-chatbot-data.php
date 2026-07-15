<?php

$root          = dirname( __DIR__ );
$current_path  = $root . '/data/meta-chatbot/knowledge-current.json';
$future_path   = $root . '/data/meta-chatbot/scheduled-2026-09-01.json';
$facts_path    = $root . '/data/meta-chatbot/programme-facts-current.json';
$training_path = $root . '/data/meta-chatbot/meta-training-current.txt';
$errors        = array();

function read_json_file( $path, &$errors ) {
	$content = file_get_contents( $path );
	$data    = json_decode( $content, true );
	if ( JSON_ERROR_NONE !== json_last_error() ) {
		$errors[] = basename( $path ) . ': ' . json_last_error_msg();
		return array();
	}
	return $data;
}

function assert_whole_shilling_prices( $value, $path, &$errors, $inside_price = false ) {
	if ( is_array( $value ) ) {
		foreach ( $value as $key => $child ) {
			$is_price = $inside_price || in_array( (string) $key, array( 'fee', 'fees', 'fee_range', 'minimum', 'maximum', 'tuition', 'exam', 'total', 'course', 'subsidised', 'standard' ), true );
			assert_whole_shilling_prices( $child, $path . '.' . $key, $errors, $is_price );
		}
		return;
	}
	if ( $inside_price && is_float( $value ) ) {
		$errors[] = "Pricing value contains cents at {$path}";
	}
}

$current  = read_json_file( $current_path, $errors );
$future   = read_json_file( $future_path, $errors );
$facts    = read_json_file( $facts_path, $errors );
$training = file_get_contents( $training_path );

assert_whole_shilling_prices( $current, 'current', $errors );
assert_whole_shilling_prices( $future, 'scheduled', $errors );

$expected_current = array(
	'electrical_solar_t1_t2'                    => array( 44050, '3 months' ),
	'solar_t1_t2_upskilling'                    => array( 16950, '13 days' ),
	'advanced_mig_mag_welding_vr'               => array( 46950, '3 months' ),
	'advanced_mig_mag_welding_vr_upskilling'    => array( 16950, '1 month' ),
	'german_a1_a2'                              => array( 110000, '6 months' ),
	'french_a1_a2'                              => array( 36250, '3 months' ),
);

$courses = array();
foreach ( $current['courses'] ?? array() as $course ) {
	$courses[ $course['id'] ?? '' ] = $course;
}

foreach ( $expected_current as $id => $expected ) {
	if ( ! isset( $courses[ $id ] ) ) {
		$errors[] = "Missing current course: {$id}";
		continue;
	}
	if ( $expected[0] !== (int) ( $courses[ $id ]['fee']['amount'] ?? 0 ) ) {
		$errors[] = "Unexpected current fee for {$id}";
	}
	if ( $expected[1] !== ( $courses[ $id ]['duration']['display'] ?? '' ) ) {
		$errors[] = "Unexpected duration for {$id}";
	}
}

if ( '2026-08-31' !== ( $current['valid_until'] ?? '' ) ) {
	$errors[] = 'Current knowledge must expire on 2026-08-31.';
}
if ( true !== ( $current['pricing_policy']['quote_only_active_records'] ?? false ) ) {
	$errors[] = 'Current pricing policy must quote only active records.';
}
if ( false !== ( $current['pricing_policy']['future_schedule_approved'] ?? null ) ) {
	$errors[] = 'Future schedule must remain unapproved in current knowledge.';
}
if ( 'quarantined_future_schedule' !== ( $future['status'] ?? '' ) || false !== ( $future['approved_for_customer_answers'] ?? null ) ) {
	$errors[] = 'Scheduled knowledge must remain quarantined and unapproved.';
}
if ( '2026-09-01' !== ( $future['effective_from'] ?? '' ) ) {
	$errors[] = 'Scheduled effective date must be 2026-09-01.';
}

$forbidden_before_activation = array( '120,961.50', '176,276', '30,683.02', '30,683.50' );
$current_text = file_get_contents( $current_path ) . "\n" . $training;
foreach ( $forbidden_before_activation as $amount ) {
	if ( false !== strpos( $current_text, $amount ) ) {
		$errors[] = "Future amount leaked into current knowledge: {$amount}";
	}
}

$facts_text = file_get_contents( $facts_path );
if ( preg_match( '/"(fee|fees|amount|total|tuition)"\s*:/i', $facts_text ) ) {
	$errors[] = 'Price-free programme facts contain a pricing field.';
}
if ( count( $facts['flagship_programmes'] ?? array() ) < 7 ) {
	$errors[] = 'Programme facts must contain all seven flagship programme families.';
}

foreach ( array( '44,050', '16,950', '46,950', '110,000', '36,250' ) as $amount ) {
	if ( false === strpos( $training, $amount ) ) {
		$errors[] = "Current training answer is missing amount: {$amount}";
	}
}

if ( $errors ) {
	fwrite( STDERR, "Meta chatbot data validation failed:\n- " . implode( "\n- ", $errors ) . "\n" );
	exit( 1 );
}

echo 'Meta chatbot data validation passed: ' . count( $courses ) . " current courses; future schedule quarantined.\n";
