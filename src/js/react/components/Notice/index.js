// eslint-disable-next-line no-unused-vars
import React, { useEffect } from 'react';
import PropTypes from 'prop-types'; // ES6
import { speak } from '@wordpress/a11y';
import { Notice as WPNotice } from '@wordpress/components';
import classNames from 'classnames';

const Notice = ( props ) => {
	const { message, status, politeness, icon, className, inline, children } = props;

	useEffect( () => {
		speak( message, politeness );
	}, [ message, status, politeness ] );

	const hasIcon = () => {
		return icon !== null;
	};
	const getIcon = ( Icon ) => {
		return <Icon width={ 16 } height={ 16 } fill="#6c757d" />;
	};

	const containerClasses = classNames( className, 'sce-admin__notice', {
		'sce-admin__notice--has-icon': hasIcon(),
		[ `sce-admin__notice-type--${ status }` ]: true,
		[ `sce-admin__notice-appearance--inline` ]: inline,
		[ `sce-admin__notice-appearance--block` ]: ! inline,
	} );
	return (
		<div className={ containerClasses }>
			<WPNotice isDismissible={ false } spokenMessage={ message } actions={ [] } { ...props }>
				{ hasIcon() &&
					<div className="sce-admin__notice-icon">{ getIcon( icon ) }</div>
				}
				<div className="sce-admin__notice-message"><>{ message } { children } </></div>
			</WPNotice>
		</div>
	);
};

Notice.defaultProps = {
	message: '',
	status: 'info',
	politeness: 'polite',
	icon: null,
	className: '',
	inline: false,
};

Notice.propTypes = {
	message: PropTypes.string.isRequired,
	status: PropTypes.oneOf( [ 'info', 'warning', 'success', 'error' ] ),
	politeness: PropTypes.oneOf( [ 'assertive', 'polite' ] ),
	icon: PropTypes.func,
	className: PropTypes.string,
	inline: PropTypes.bool,
};

export default Notice;
