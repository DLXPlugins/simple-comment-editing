import React from 'react';
import Skeleton from 'react-loading-skeleton';

const AdminThemeDefaultsLoader = () => {
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
					<div className="qdlx-admin__themes--wrapper">
						<div className="qdlx-admin__themes--content-inner">
							<div className="qdlx-admin__themes--content-panel">
								<div className="qdlx-admin__themes--content-panel-body">
									<Skeleton height="220px" width="100%" />
									<div className="qdlx-admin__themes--content-panel-heading">
										<h3><Skeleton width="100%" /></h3>
									</div>
									<div className="qdlx-admin__themes--meta-top">
										<div className="qdlx-admin__themes--meta-designer">
											<Skeleton width="100%" />
										</div>
									</div>
									<div className="qdlx-admin__themes--button-select">
										<Skeleton width="100%" count={ 1 } />
									</div>

								</div>
							</div>
							<div className="qdlx-admin__themes--content-panel">
								<div className="qdlx-admin__themes--content-panel-body">
									<Skeleton height="220px" width="100%" />
									<div className="qdlx-admin__themes--content-panel-heading">
										<h3><Skeleton width="100%" /></h3>
									</div>
									<div className="qdlx-admin__themes--meta-top">
										<div className="qdlx-admin__themes--meta-designer">
											<Skeleton width="100%" />
										</div>
									</div>
									<div className="qdlx-admin__themes--button-select">
										<Skeleton width="100%" count={ 1 } />
									</div>

								</div>
							</div>
							<div className="qdlx-admin__themes--content-panel">
								<div className="qdlx-admin__themes--content-panel-body">
									<Skeleton height="220px" width="100%" />
									<div className="qdlx-admin__themes--content-panel-heading">
										<h3><Skeleton width="100%" /></h3>
									</div>
									<div className="qdlx-admin__themes--meta-top">
										<div className="qdlx-admin__themes--meta-designer">
											<Skeleton width="100%" />
										</div>
									</div>
									<div className="qdlx-admin__themes--button-select">
										<Skeleton width="100%" count={ 1 } />
									</div>

								</div>
							</div>
							<div className="qdlx-admin__themes--content-panel">
								<div className="qdlx-admin__themes--content-panel-body">
									<Skeleton height="220px" width="100%" />
									<div className="qdlx-admin__themes--content-panel-heading">
										<h3><Skeleton width="100%" /></h3>
									</div>
									<div className="qdlx-admin__themes--meta-top">
										<div className="qdlx-admin__themes--meta-designer">
											<Skeleton width="100%" />
										</div>
									</div>
									<div className="qdlx-admin__themes--button-select">
										<Skeleton width="100%" count={ 1 } />
									</div>

								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	);
};

export default AdminThemeDefaultsLoader;
