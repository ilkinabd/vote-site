const path = require('path');
const VueLoaderPlugin = require('vue-loader/lib/plugin');

module.exports = {
    entry: './src/main',
    mode: 'development',
    output: {
        path: path.resolve(__dirname, 'dist'),
        filename: 'app.js'
    },
    resolve: {
        alias: {
            vue: 'vue/dist/vue.js'
        }
    },
    module: {
        rules: [
            {
                test: /\.vue$/,
                loader: 'vue-loader'
            },
            {
                test: /\.js$/,
                loader: 'babel-loader'
            },
            {
                test: /\.scss$/,
                use: [
                    {
                        loader: 'style-loader'
                    },
                    {
                        loader: 'css-loader'
                    },
                    {
                        loader: 'sass-loader'
                    }
                ]
            },
            {
                test: /\.(eot|svg|ttf|woff|woff2)$/,
                loader: 'url-loader',
                options: {
                    name: '[name].[ext]',
                    outputPath: 'fonts',
                }
            }
        ]
    },
    plugins: [
        // убедитесь что подключили плагин!
        new VueLoaderPlugin()
    ]
}