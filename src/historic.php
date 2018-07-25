<?php
/**
 * Return historic data.
 */

date_default_timezone_set('UTC');

function get_data_range( $rows, $from, $to ) {
	$rangeData = [];

	foreach ( $rows as $row ) {
		if ( $row[0] >= $from && $row[0] <= $to ) {
			$rangeData[] = $row;
		}
	}

	return $rangeData;
}

$refStart = intval( strtotime( '-24 hours' ) / 60 );
$allRange = range( $refStart, $refStart + 60 * 24 );

$minuteHistory = [];

foreach ( $allRange as $minutes ) {
	$filename = sprintf(
		'%s/data/%s-eth-usd.csv',
		dirname( __DIR__ ),
		date( 'Ymd', $minutes * 60 )
	);

	if ( ! file_exists( $filename ) ) {
		continue;
	}

	$dataRows = explode( "\n", file_get_contents( $filename ) );

	// Remove first and last row.
	array_shift( $dataRows );
	array_pop( $dataRows );

	$rowData = [];
	foreach ( $dataRows as $row ) {
		$rowData[] = explode( ',', $row );
	}

	$rangeData = get_data_range( $rowData, $minutes * 60, $minutes * 60 + 59 );

	if ( ! empty( $rangeData ) ) {
		$fiatValues = array_column( $rangeData, 3 );
		$volumes = array_column( $rangeData, 5 );

		$minuteHistory[] = [
			'time' => $minutes * 60,
			'open' => $fiatValues[0],
			'close' => end( $fiatValues ),
			'high' => max( $fiatValues ),
			'low' => min( $fiatValues ),
			'volumefrom' => $volumes[0],
			'volumeto' => end( $volumes ),
		];
	} else {
		$minuteHistory[] = [
			'time' => $minutes * 60,
			'open' => 0,
			'close' => 0,
			'high' => 0,
			'low' => 0,
			'volumefrom' => 0,
			'volumeto' => 0,
		];
	}
}

array_walk( $minuteHistory, function( $value ) {
	return floatval( $value );
} );

header( 'Access-Control-Allow-Origin: *' );
header( 'Content-Type: application/json' );

echo json_encode( [
	'TimeFrom' => $refStart,
	'TimeTo' => $refStart + 60 * 24,
	'Response' => 'Success',
	'Data' => $minuteHistory
] );
