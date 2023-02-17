import React from 'react';
import { createRoot } from 'react-dom/client';
import Mailchimp from './Mailchimp';

const container = document.getElementById( 'sce-tab-mailchimp' );
const root = createRoot( container );
root.render(
	<React.StrictMode>
		<Mailchimp />
	</React.StrictMode>
);