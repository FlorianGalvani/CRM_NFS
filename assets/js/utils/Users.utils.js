import axios from 'axios';
import { Cookie } from 'utils/index';

const token = Cookie.getCookie('token')
const basicUrl = 'http://localhost:8000/api/users'
const config = {
    headers: {
        Authorization: `Bearer ${token}`
    }
}

export class Users {

    static getAllUsers = () => {
        axios.get(basicUrl, config)
            .then(response => response.data)
            .catch(error => console.error(`Error: ${error}`))
        console.log(token)
    }

    static getUser = (id) => {

    }

    static createUser = (user) => {

    }

    static updateUser = (id, user) => {

    }

    static deleteUser = (id) => {

    }
}