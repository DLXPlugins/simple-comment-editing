/* eslint-disable no-unused-vars */
import React from 'react';
import classnames from 'classnames';
import PropTypes from 'prop-types';
import Spinner from '../../components/Spinner';

const Button = ( props ) => {
	const { Component } = props;
	return (
		<>
			{ props.show && (
				<button
					className={ classnames( `${ props.className }`, {
						'mr-button': true,
						'mr-input-disabled': props.disabled ? true : false,
					} ) }
					onClick={ ( e ) => {
						if ( props.disabled ) {
							e.preventDefault();
						} else if ( null !== props.onClick ) {
							props.onClick( e );
						}
					} }
					type={ props.type }
				>
					<>
						{ ! props.loading && '' !== props.label && (
							<>
								{ props.label }
								{ null !== Component && props.showIcon === true &&
									<>
										&nbsp;&nbsp;
										<Component />
									</>
								}
							</>
						) }
						{ props.loading && '' !== props.labelLoading && (
							<>
								{ props.labelLoading }
								{ props.showIcon && <>&nbsp;&nbsp;</> }
								{ null !== Component && <Component /> }
							</>
						) }
						{ props.showIcon && props.loading && <Spinner /> }
					</>
				</button>
			) }
		</>
	);
};
Button.defaultProps = {
	Component: null,
	id: '',
	className: '',
	showIcon: true,
	icon: 'save',
	loading: false,
	show: false,
	disabled: false,
	label: '',
	labelLoading: '',
	onClick: null,
	type: 'button',
};
export default Button;
