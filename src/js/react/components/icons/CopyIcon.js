import React from 'react';
import PropTypes from 'prop-types';

const CopyIcon = ( props ) => {
	return (
		<svg
			xmlns="http://www.w3.org/2000/svg"
			viewBox="0 0 512 512"
			width={ props.width }
			height={ props.height }
		>
			<path
				fill={ props.fill }
				d="M384 96V0H272c-26.51 0-48 21.49-48 48v288c0 26.51 21.49 48 48 48h192c26.51 0 48-21.49 48-48V128h-95.1c-18.5 0-32.9-14.4-32.9-32zm32-96v96h96L416 0zM192 352V128H48c-26.51 0-48 21.49-48 48v288c0 26.51 21.49 48 48 48h192c26.51 0 48-21.49 48-48v-48h-32c-35.3 0-64-28.7-64-64z"
			/>
		</svg>
	);
};

CopyIcon.defaultProps = {
	width: 24,
	height: 24,
	fill: '#000000',
};

CopyIcon.propTypes = {
	width: PropTypes.number,
	height: PropTypes.number,
	fill: PropTypes.string,
};

export default CopyIcon;
