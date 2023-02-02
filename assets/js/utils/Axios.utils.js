import axios from "axios";
import {Cookie} from "./Cookie.utils";

export class Axios {
    static baseUrl = 'http://localhost:8000/api';
    static token = Cookie.getCookie("token");

    static setAuthorization(token) {
        if (token) axios.defaults.headers.common['Authorization'] = 'Bearer ' + token;
        else delete axios.defaults.headers.common['Authorization'];
    }

    static requestConfig = {
        headers: {
            'Authorization': 'Bearer ' + this.token
        }
    }

    static async get(path) {
        const response = [];
        await axios.get(this.baseUrl+path).then((res) => {
            response['success'] = true;
            response['data'] = res.data;
        }).catch((err) => {
            response['error'] = true;
            response['data'] = err.response.data;
        })
        return response;
    }

    static async post(path, data) {
        const response = [];
        await axios.post(this.baseUrl+path, data).then((res) => {
            response['success'] = true;
            response['data'] = res.data;
        }, (err) => {
            response['error'] = true;
            response['data'] = err.response.data;
        });
        return response;
    }
}