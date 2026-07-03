// Import the functions you need from the SDKs you need
import { initializeApp } from "firebase/app";
import { getAnalytics } from "firebase/analytics";
// TODO: Add SDKs for Firebase products that you want to use
// https://firebase.google.com/docs/web/setup#available-libraries

// Your web app's Firebase configuration
// For Firebase JS SDK v7.20.0 and later, measurementId is optional
const firebaseConfig = {
 apiKey: "AIzaSyBasSr4n243V1KfTRCSW93ONMVPjz3CePA",
 authDomain: "vibe-coding-whoisdesir.firebaseapp.com",
 projectId: "vibe-coding-whoisdesir",
 storageBucket: "vibe-coding-whoisdesir.firebasestorage.app",
 messagingSenderId: "53544450916",
 appId: "1:53544450916:web:f1fc07f9c0b491b6839871",
 measurementId: "G-CB9N68072L"
};

// Initialize Firebase
const app = initializeApp(firebaseConfig);
const analytics = getAnalytics(app);
