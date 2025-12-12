import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue'
import dotenv from "dotenv";

const root = "resources/js/components/";

const { config } = dotenv;

const env = config().parsed;

export default defineConfig({
    plugins: [
        vue(),
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],

    server: {
            host: env.VITE_HOST || 'localhost', //to enable view in other device
            cors: {
                origin: '*',
            },
    },

    // server: {
    //     host: '0.0.0.0',
    //     cors : {
    //         origin: 'http://10.169.141.202:8000',
    //         // origin: '*',
    //         credentials: true
    //     },
    //     hmr: {
    //         host: '10.169.141.202',
    //         clientPort: 5173
    //     }
    // },

    resolve: {
        alias: {
            "@aspect": `./resources/components/aspects`,
            "@pages": `./resources/components/pages`,
            "@includes": `./resources/components/includes`
            }
        // alias: [
            
        //   {
        //     find: "@js",
        //     replacement: path.resolve(__dirname, `resources/js`),
        //   },
        //   {
        //     find: "@pages",
        //     replacement: path.resolve(__dirname, `${root}pages`),
        //   },
        //   {
        //     find: "@aspects",
        //     replacement: path.resolve(__dirname, `${root}aspects`),
        //   },
        //   {
        //     find: "@templates",
        //     replacement: path.resolve(__dirname, `${root}templates`),
        //   },
        //   {
        //     find: "@auth",
        //     replacement: path.resolve(__dirname, `${root}auth`),
        //   },
        // ],
    },
});