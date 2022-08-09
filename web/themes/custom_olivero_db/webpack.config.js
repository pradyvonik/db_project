const path = require("path");

const miniCss = require("mini-css-extract-plugin");

module.exports = {
  entry: {
    dscss: ["./src/index.js"],
    javascript: ["./src/Drupal.js"]
  },
  output: {
    filename: "[name].js",
    path: path.resolve(__dirname, "dist")
  },
  module: {
    rules: [
      {
        test: /\.(s*)css$/,
        use: [miniCss.loader, "css-loader", "sass-loader"]
      },
      {
        test: /\.(jpg|png|gif|woff|eot|ttf|svg)/,
        use: {
          loader: "file-loader",
          options: {
            esModule: false
          }
        }
      }
    ]
  },
  plugins: [
    // eslint-disable-next-line new-cap
    new miniCss({
      filename: "style.css"
    })
  ]
};
