import React from 'react';
import Skeleton from 'react-loading-skeleton';

const AdminDefaultsLoader = () => {
	return (
		<div className="qdlx-admin__tabs--content-wrap">
			<div className="qdlx-admin__tabs--content-panel">
				<div className="qdlx-admin__tabs--content-heading">
					<h1>
						<Skeleton circle={ true } width="45px" height="45px" inline={ true } />
						<span className="qdlx-admin__heading--text">
							<Skeleton width="400px" height="1.8em" inline={ true } />
						</span>
					</h1>
					<p className="description">
						<Skeleton width="100%" height="0.9em" inline={ false } />
					</p>
				</div>
				<div className="qdlx-admin__tabs--content-inner">
					<Skeleton width="100%" height="2.8em" inline={ false } count={ 2 } />
				</div>
				<div className="qdlx-admin__tabs--content-inner">
					<Skeleton width="100%" height="2.8em" inline={ false } count={ 2 } />
				</div>
				<div className="qdlx-admin__tabs--content-inner">
					<Skeleton width="100%" height="2.8em" inline={ false } count={ 2 } />
				</div>
			</div>
		</div>
	);
};

export default AdminDefaultsLoader;
