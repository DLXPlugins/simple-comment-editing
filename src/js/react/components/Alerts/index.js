/* eslint-disable no-unused-vars */
import * as React from 'react';
import classNames from 'classnames';

const Alerts = ( props ) => {
	const { alertType, message } = props;

	const classes = classNames( {
		alert: true,
		success: 'success' === alertType,
		info: 'info' === alertType,
		warning: 'warning' === alertType,
		error: 'error' === alertType,
	} );
	return (
		<>
			<div className={ classes }>{ message }</div>
		</>
	);
};

export default Alerts;
