const { defineConfig } = require('@vue/cli-service')

module.exports = defineConfig({
  transpileDependencies: true,
  devServer: {
    client: {
      overlay: false
    },
    proxy: {
      '/api.php': {
        target: 'http://127.0.0.1:8000',
        changeOrigin: true
      }
    }
  }
})