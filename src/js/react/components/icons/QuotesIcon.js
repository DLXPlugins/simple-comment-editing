import React from 'react';
import PropTypes from 'prop-types';

const QuotesIcon = ( props ) => {
	return (
		<svg
			viewBox="0 0 4450 4450"
			xmlns="http://www.w3.org/2000/svg"
			xmlSpace="preserve"
			style={ {
				fillRule: 'evenodd',
				clipRule: 'evenodd',
				strokeLinejoin: 'round',
				strokeMiterlimit: 2,
			} }
			width={ props.width }
			height={ props.height }
		>
			<path d="M2224.495 4449.097c1225.292 0 2224.626-999.334 2224.626-2224.502 0-1225.26-999.334-2224.59-2224.626-2224.59C999.339.006.005 999.336.005 2224.596c0 1225.168 999.334 2224.502 2224.49 2224.502Z" fill={ props.fill } />
			<path
				d="M645.766 833.41c72.396-19.418 146.805 23.555 166.223 95.952 26.731 99.748-16.611 257.328-201.629 281.878 40.111-41.13 65.254-71.09 75.658-111.12-61.938 2.38-119.509-38.12-136.205-100.515-19.417-72.397 23.555-146.807 95.953-166.195ZM988.445 833.41c72.395-19.418 146.835 23.555 166.225 95.952 26.73 99.748-16.58 257.328-201.601 281.878 40.082-41.13 65.221-71.09 75.631-111.12-61.939 2.38-119.512-38.12-136.207-100.515-19.389-72.397 23.556-146.807 95.952-166.195Z"
				fill={ props.secondaryFill }
				transform="translate(-1325.8 -2025.45) scale(4.16667)"
			/>
		</svg>
	);
};

QuotesIcon.defaultProps = {
	width: 24,
	height: 24,
	fill: '#000000',
	secondaryFill: '#FFFFFF',
};

QuotesIcon.propTypes = {
	width: PropTypes.number,
	height: PropTypes.number,
	fill: PropTypes.string,
	secondaryFill: PropTypes.string,
};

export default QuotesIcon;
