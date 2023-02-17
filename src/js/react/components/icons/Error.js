/* eslint-disable no-unused-vars */
import React from 'react';

function Error( props ) {
	return (
		<div className="alert gforms_note_error" role="alert">{ props.error }</div>
	);
}

export default Error;
