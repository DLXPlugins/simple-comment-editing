import React from 'react';
import Skeleton from 'react-loading-skeleton';

const AdminThemeColorsLoader = ( props ) => {
	const { showHeader } = props;
	return (
		<div className="qdlx-admin__tabs--content-wrap">
			<div className="qdlx-admin__tabs--content-panel">
				{ showHeader && (
					<div className="qdlx-admin__tabs--content-heading">
						<h1>
							<Skeleton
								circle={ true }
								width="45px"
								height="45px"
								inline={ true }
							/>
							<span className="qdlx-admin__heading--text">
								<Skeleton width="400px" height="1.8em" inline={ true } />
							</span>
						</h1>
						<p className="description">
							<Skeleton width="100%" height="0.9em" inline={ false } />
						</p>
					</div>
				) }
				<div className="qdlx-admin__tabs--content-heading">
					<p className="description">
						<Skeleton width="100%" height="0.9em" inline={ false } count={ 2 } />
					</p>
				</div>
				<div className="qdlx-admin__tabs--content-inner">
					<Skeleton
						width="80%"
						height="300px"
						inline={ false }
						style={ { margin: '0 auto' } }
						className="no-flex"
					/>
				</div>
				<div
					className="qdlx-admin__tabs--content-inner"
					style={ { textAlign: 'center' } }
				>
					<Skeleton
						width="150px"
						height="30px"
						inline={ true }
						style={ { margin: '0 auto' } }
					/>
					<Skeleton
						circle={ true }
						width="30px"
						height="30px"
						inline={ true }
						style={ { marginLeft: '15px' } }
					/>
				</div>
				<div
					className="qdlx-admin__tabs--content-inner"
					style={ { textAlign: 'center' } }
				>
					<Skeleton
						width="150px"
						height="30px"
						inline={ true }
						style={ { margin: '0 auto' } }
					/>
					<Skeleton
						circle={ true }
						width="30px"
						height="30px"
						inline={ true }
						style={ { marginLeft: '15px' } }
					/>
				</div>
				<div
					className="qdlx-admin__tabs--content-inner"
					style={ { textAlign: 'center' } }
				>
					<Skeleton
						width="150px"
						height="30px"
						inline={ true }
						style={ { margin: '0 auto' } }
					/>
					<Skeleton
						circle={ true }
						width="30px"
						height="30px"
						inline={ true }
						style={ { marginLeft: '15px' } }
					/>
				</div>
				<div className="qdlx-admin__tabs--content-inner">
					<Skeleton width="100%" height="45px" inline={ false } count={ 1 } />
				</div>
			</div>
		</div>
	);
};

export default AdminThemeColorsLoader;
