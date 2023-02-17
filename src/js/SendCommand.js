/* eslint-disable no-undef */
/* eslint-disable camelcase */
import axios from 'axios';
import qs from 'qs';

export default function sendCommand( action, data ) {
	const params = {
		action,
	};

	const default_data = {
		nonce: false,
		action,
	};

	if ( 'undefined' === typeof data ) {
		data = {};
	}

	for ( const opt in default_data ) {
		if ( ! data.hasOwnProperty( opt ) ) {
			data[ opt ] = default_data[ opt ];
		}
	}

	const options = {
		method: 'post',
		url: ajaxurl,
		params,
		paramsSerializer( jsparams ) {
			return qs.stringify( jsparams, { arrayFormat: 'brackets' } );
		},
		data: qs.stringify( data ),
	};

	return axios( options );
}
