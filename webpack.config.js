var path = require('path');
var ExtractTextPlugin = require('extract-text-webpack-plugin');

var extractPlugin = new ExtractTextPlugin({
    filename: 'Main.css'
});

module.exports = {
    entry: './Resources/Private/Components/Main.js',
    output: {
        path: path.resolve(__dirname, 'Resources', 'Public'),
        filename: 'Main.js'
    },
    module: {
        rules: [
            {
                test: /\.js$/,
                use: [{
                    loader: 'babel-loader',
                    options: {
                        presets: ['es2015']
                    }
                }]
            },
            {
                test: /\.(scss)$/,
                use: extractPlugin.extract({
                    use: [
                        { loader: 'css-loader' },
                        { loader: 'autoprefixer-loader' },
                        { loader: 'sass-loader' }
                    ]
                })
            },
        ]
    },
    plugins: [
        extractPlugin
    ]
};
