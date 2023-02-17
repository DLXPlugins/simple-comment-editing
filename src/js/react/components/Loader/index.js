import React, { useState } from 'react';
import PropTypes from 'prop-types';
import BeatLoader from 'react-spinners/BeatLoader';
import Section from '../Section';

const override = {
	display: 'block',
	margin: '0 auto',
	borderColor: 'red',
};

const Loader = ( props ) => {
	const [ loading, setLoading ] = useState( true );
	const [ color, setColor ] = useState( '#309FD5' );

	const { title, description, showStatusPill, status, statusLabel } = props;

	return (
		<>
			<Section
				title={ title }
				description={ description }
				showStatusPill={ showStatusPill }
				status={ status }
				statusLabel={ statusLabel }
			>
				<BeatLoader
					color={ color }
					loading={ loading }
					cssOverride={ override }
					size={ 25 }
					speedMultiplier={ 0.65 }
				/>
			</Section>
		</>
	);
};

Loader.defaultProps = {
	title: '',
	description: '',
	showStatusPill: false,
	status: 'success',
	statusLabel: '',
};

Loader.propTypes = {
	title: PropTypes.string.isRequired,
	description: PropTypes.string.isRequired,
	showStatusPill: PropTypes.bool,
	status: PropTypes.string,
	statusLabel: PropTypes.string,
};

export default Loader;
