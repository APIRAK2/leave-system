const { defineConfig } = require('@vue/cli-service')

module.exports = defineConfig({
  transpileDependencies: true,
  devServer: {
    proxy: {
      '/api.php': {
        target: 'http://localhost/leave-api',
        changeOrigin: true
      }
    }
  }
})