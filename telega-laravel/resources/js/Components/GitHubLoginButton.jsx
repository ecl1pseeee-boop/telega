import React from 'react';

const GithubLoginButton = () => {
    return (
        <a
            href="/auth/github"
            className="flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-gray-900 hover:bg-gray-800"
        >
            Войти через GitHub
        </a>
    );
};

export default GithubLoginButton;
