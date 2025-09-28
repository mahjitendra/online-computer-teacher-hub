const path = require('path');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');

module.exports = {
  entry: {
    main: './public/assets/js/frontend/main.js',
    'course-player': './public/assets/js/frontend/course-player.js',
    'exam-system': './public/assets/js/frontend/exam-system.js',
    'job-search': './public/assets/js/frontend/job-search.js',
    admin: './public/assets/js/admin/dashboard.js',
    teacher: './public/assets/js/teacher/course-creator.js'
  },
  output: {
    path: path.resolve(__dirname, 'public/assets/dist'),
    filename: 'js/[name].bundle.js',
    clean: true
  },
  module: {
    rules: [
      {
        test: /\.js$/,
        exclude: /node_modules/,
        use: {
          loader: 'babel-loader',
          options: {
            presets: ['@babel/preset-env']
          }
        }
      },
      {
        test: /\.scss$/,
        use: [
          MiniCssExtractPlugin.loader,
          'css-loader',
          'sass-loader'
        ]
      },
      {
        test: /\.css$/,
        use: [
          MiniCssExtractPlugin.loader,
          'css-loader'
        ]
      },
      {
        test: /\.(png|jpg|jpeg|gif|svg)$/,
        type: 'asset/resource',
        generator: {
          filename: 'images/[name][ext]'
        }
      },
      {
        test: /\.(woff|woff2|eot|ttf|otf)$/,
        type: 'asset/resource',
        generator: {
          filename: 'fonts/[name][ext]'
        }
      }
    ]
  },
  plugins: [
    new MiniCssExtractPlugin({
      filename: 'css/[name].bundle.css'
    })
  ],
  optimization: {
    splitChunks: {
      chunks: 'all',
      cacheGroups: {
        vendor: {
          test: /[\\/]node_modules[\\/]/,
          name: 'vendors',
          chunks: 'all'
        }
      }
    }
  },
  resolve: {
    alias: {
      '@': path.resolve(__dirname, 'public/assets/js'),
      '@css': path.resolve(__dirname, 'public/assets/css'),
      '@images': path.resolve(__dirname, 'public/assets/images')
    }
  },
  devtool: process.env.NODE_ENV === 'production' ? false : 'source-map'
};