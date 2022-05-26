module.exports = {
    plugins: [
        {
            name: 'preset-default',
            params: {
                overrides: {
                    // or disable plugins
                    cleanupIDs: false,
                    removeViewBox: false,
                },
            },
        },
    ],
};