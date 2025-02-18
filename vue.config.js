module.exports = {
    devServer: {
      proxy: {
        '/rss': {
          target: 'https://rsshub.app',
          changeOrigin: true,
          pathRewrite: {
            '^/rss': '/telegram/channel/analitikgroup_official', // Прокси для конкретного канала
          },
        },
      },
    },
  };
  