import Navbar from './components/Navbar'
import Hero from './components/Hero'
import About from './components/About'
import Topics from './components/Topics'
import PastTalks from './components/PastTalks'
import Contact from './components/Contact'
import Footer from './components/Footer'

export default function App() {
  return (
    <>
      <Navbar />
      <main>
        <Hero />
        <About />
        <Topics />
        <PastTalks />
        <Contact />
      </main>
      <Footer />
    </>
  )
}
