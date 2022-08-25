export const setToken = (token) => {
    document.cookie = `BEARER=${token}`;
}

export const setRefreshToken = (token, rememberMe) => {
    let expires = '';
    if (rememberMe) {
        let date = new Date();
        let expDays = 90;
        date.setTime(date.getTime() + (expDays * 24 * 60 * 60 * 1000));
        expires = `; expires=${date.toUTCString()}`;
    }
    document.cookie = `REFRESH_TOKEN=${token}${expires}`;
}

export const logout = () => {
    window.localStorage.removeItem('token');
    document.cookie = `BEARER=`;
}
