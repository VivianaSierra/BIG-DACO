import { initializeApp } from 'firebase/app';
import { getAnalytics, isSupported } from 'firebase/analytics';
import { getAuth, signInWithEmailAndPassword } from 'firebase/auth';

const firebaseConfig = {
    apiKey: 'AIzaSyDicZxPiD4ucomnYFw5KfGwjNUxa36kbUQ',
    authDomain: 'big-daco.firebaseapp.com',
    projectId: 'big-daco',
    storageBucket: 'big-daco.firebasestorage.app',
    messagingSenderId: '669791099142',
    appId: '1:669791099142:web:921fc21edf6b14a473e687',
    measurementId: 'G-6NGY3B50F4',
};

const app = initializeApp(firebaseConfig);
isSupported().then((ok) => ok && getAnalytics(app));

const auth = getAuth(app);

const form = document.getElementById('firebase-login-form');

if (form) {
    const errorBox = document.getElementById('firebase-login-error');
    const button = form.querySelector('button[type="submit"]');

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        errorBox.textContent = '';
        button.disabled = true;

        try {
            const credential = await signInWithEmailAndPassword(
                auth,
                form.email.value,
                form.password.value,
            );
            form.id_token.value = await credential.user.getIdToken();
            form.password.value = '';
            form.submit();
        } catch (e) {
            errorBox.textContent = 'Credenciales incorrectas o usuario no autorizado.';
            button.disabled = false;
        }
    });
}
