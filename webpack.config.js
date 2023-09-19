const path = require( 'path' );
const RemoveEmptyScripts = require('webpack-remove-empty-scripts');
const MiniCssExtractPlugin = require( 'mini-css-extract-plugin' );

module.exports = ( env ) => {
	return [
		{
			mode: env.mode,
			entry: {
				'sce-admin': [ './src/scss/admin.scss' ],
				'sce-frontend': [ './src/scss/frontend.scss' ],
			},
			output: {
				clean: true,
			},
			module: {
				rules: [
					{
						test: /\.scss$/,
						exclude: /(node_modules|bower_components)/,
						use: [
							{
								loader: MiniCssExtractPlugin.loader,
							},
							{
								loader: 'css-loader',
								options: {
									sourceMap: true,
									url: false,
								},
							},
							'sass-loader',
						],
					},
				],
			},
			devtool: 'source-map',
			plugins: [ new RemoveEmptyScripts(), new MiniCssExtractPlugin() ],
		},
		{
			mode: env.mode,
			resolve: {
				alias: {
					react: path.resolve( 'node_modules/react' ),
				},
			},
			devtool: 'production' === env.mode ? false : 'source-map',
			entry: {
				'integrations-admin': [ './src/js/react/views/integrations/index.js' ],
				'sce-editing': [ './src/js/comment-editing/editing.js' ],

			},
			module: {
				rules: [
					{
						test: /\.(js|jsx)$/,
						exclude: /(node_modules|bower_components)/,
						loader: 'babel-loader',
						options: {
							presets: [ '@babel/preset-env', '@babel/preset-react' ],
							plugins: [
								'@babel/plugin-proposal-class-properties',
								'@babel/plugin-transform-arrow-functions',
							],
						},
					},
					{
						test: /\.scss$/,
						exclude: /(node_modules|bower_components)/,
						use: [
							{
								loader: MiniCssExtractPlugin.loader,
							},
							{
								loader: 'css-loader',
								options: {
									sourceMap: true,
									url: false,
								},
							},
							'sass-loader',
						],
					},
				],
			},
			plugins: [ new RemoveEmptyScripts(), new MiniCssExtractPlugin() ],
		},
	];
};
