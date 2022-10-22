import PropTypes from 'prop-types'; // ES6
import styled from 'styled-components';

const IconCircle = ( props ) => {
	const Circle = styled.div`
		width: ${ props.width }px;
		height: ${ props.height }px;
		background-image: ${ props.gradient.replace( ';', '' ) };
		border-radius: 50%;
		margin-right: 6px;
	`;
	return (
		<Circle className="quotes dlx quotes-dlx-icon-circle" />
	);
};

IconCircle.defaultProps = {
	stroke: '#000',
	width: 16,
	height: 16,
};

IconCircle.propTypes = {
	gradient: PropTypes.string,
	stroke: PropTypes.string,
	width: PropTypes.number,
	height: PropTypes.number,
	className: PropTypes.string,
};

export default IconCircle;
