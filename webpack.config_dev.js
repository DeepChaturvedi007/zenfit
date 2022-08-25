const path = require('path');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const ManifestPlugin = require('webpack-manifest-plugin');
const { CleanWebpackPlugin } = require('clean-webpack-plugin');
const webpack = require('webpack');
const { entries } = require('./WebpackEntries');

const config = {
  mode: 'development',
  devtool: 'source-map',
  entry: entries,
  output: {
    path: path.join(__dirname, "web/js/dist/"),
    filename: "[name].[chunkhash].js"
  },
  resolve: {
    extensions: [".tsx", ".ts", ".js"],
  },
  module: {
    rules: [
      {
        test: /\.jsx?$/,
        exclude: /(node_modules|bower_components)/,
        use: {
          loader: 'babel-loader',
          query: {
            plugins: [
              "transform-decorators-legacy",
              "transform-class-properties",
              ["transform-runtime", {
                "polyfill": false,
                "regenerator": true
              }]
            ],
            presets: ["es2015", "stage-0", "react"],
          }
        }
      },
      {
        test: /\.(jpe?g|png|gif)$/i,
        use: [
          'file-loader',
          {
            loader: 'image-webpack-loader',
            options: {
              disable: true,
              mozjpeg: {
                progressive: true,
                quality: 75
              },
              optipng: {
                // enabled: false,
              },
              pngquant: {
                quality: [0.65, 0.90],
                speed: 4
              },
              gifsicle: {
                interlaced: false,
              }
            },
          },
        ],
      },
      {
        test: /\.(sass|scss)$/,
        use: [
          {
            loader: MiniCssExtractPlugin.loader,
            options: {
              hmr: process.env.NODE_ENV === 'development',
            },
          },
          'css-loader',
          'sass-loader',
        ],
      },
      {test: /\.coffee$/, loader: "coffee-loader"},
      {test: /\.less$/, loader: "style!css!less"},
      {test: /\.css$/, loader: "style-loader!css-loader"},
      {test: /\.svg$/, loader: "url-loader?limit=100000"},
      {test: /\.jpg$/, loader: "file-loader"},
      {
        test: /\.json$/,
        loader: 'json-loader'
      },
      {
        test: /\.mp3$/,
        loader: 'file-loader'
      },
      {
        test: /\.(woff(2)?|ttf|eot|svg)(\?v=\d+\.\d+\.\d+)?$/,
        use: [
          {
            loader: 'file-loader',
            options: {
              name: '[name].[ext]',
              outputPath: 'fonts/'
            }
          }
        ]
      },
      {
        test: /\.(ts|tsx)$/,
        use: 'ts-loader',
        exclude: '/node_modules/'
      }
    ]
  },
  plugins: [
    new webpack.ProgressPlugin(),
    new CleanWebpackPlugin(),
    new MiniCssExtractPlugin({
      filename: '[name].[hash].css',
      chunkFilename: '[id].[hash].css',
      ignoreOrder: false,
    }),
    new ManifestPlugin(),
  ],
  stats: {
    children: false
  }
};

module.exports = config;
