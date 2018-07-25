<?php

date_default_timezone_set('UTC');

$fiatSybmols = [
	'USD',
	'GBP',
	'JPY',
	'CHF',
	'EUR',
	'CAD',
	'AUD',
	'KRW',
	'RUB',
	'CNY',
	'ARS',
	'HKD',
	'INR',
	'SGD',
	'AED',
];

$csvFields = [
	'fromsymbol',
	'tosymbol',
	'price',
	'lastupdate',
	'lastvolume',
];

$apiUrl = sprintf(
	'https://min-api.cryptocompare.com/data/pricemultifull?fsyms=ETH&tsyms=%s',
	implode( ',', $fiatSybmols )
);

$context = stream_context_create([
	'http' => [
		'timeout' => 10,
	]
]);

$apiResponse = json_decode( file_get_contents( $apiUrl, false, $context ) );

if ( ! isset( $apiResponse->RAW->ETH ) ) {
	error_log( 'Failed to fetch data from the API.' );
	die;
}

$filenameDate = date( 'Ymd' );
$csvFieldsApi = array_map( 'strtoupper', $csvFields );

$fileMap = [];

foreach ( $apiResponse->RAW->ETH as $refSymbol => $fields ) {
	$row = [
		time(),
	];

	foreach ( $csvFieldsApi as $csvFieldName ) {
		if ( isset( $fields->$csvFieldName ) ) {
			$row[] = $fields->$csvFieldName;
		} else {
			$row[] = null;
		}
	}

	$fileMap[] = [
		'filename' => sprintf(
			'%s/data/%s-eth-%s.csv',
			dirname( __DIR__ ),
			$filenameDate,
			strtolower( $refSymbol )
		),
		'header' => array_merge( [ 'timestamp' ], $csvFields ),
		'data' => [ $row ],
	];
}

foreach ( $fileMap as $file ) {
	if ( ! file_exists( $file['filename'] ) ) {
		file_put_contents( $file['filename'], implode( ',', $file['header'] ) . "\n" );
	}

	$rows = [];

	foreach ( $file['data'] as $rowData ) {
		$rows[] = implode( ',', $rowData );
	}

	file_put_contents( $file['filename'], implode( "\n", $rows ) . "\n", FILE_APPEND );
}
