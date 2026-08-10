const topics = [
  {
    title: 'AI in Marketing',
    description:
      'How small and mid-size businesses can leverage AI for content creation, audience targeting, campaign optimization, and media production — without a giant budget or engineering team.',
    icon: (
      <svg className="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" />
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" />
      </svg>
    ),
  },
  {
    title: 'AI for Small Business',
    description:
      'Practical AI adoption strategies for entrepreneurs: automation workflows, AI-assisted customer service, intelligent bookkeeping, and tools that actually save time and money.',
    icon: (
      <svg className="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
      </svg>
    ),
  },
  {
    title: 'Practical AI Adoption',
    description:
      'A builder\'s guide to going from AI curiosity to deployed tooling: choosing the right model, avoiding common pitfalls, measuring ROI, and building a culture that embraces AI.',
    icon: (
      <svg className="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
      </svg>
    ),
  },
]

export default function Topics() {
  return (
    <section id="topics" className="py-20 sm:py-28 bg-dark-900">
      <div className="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="text-center mb-16">
          <h2 className="text-3xl sm:text-4xl font-display font-bold mb-4">
            Keynote Topics
          </h2>
          <p className="text-silver-400 font-body max-w-2xl mx-auto">
            Each talk is tailored to the audience — part demo, part strategy session,
            and entirely grounded in real-world AI building.
          </p>
        </div>

        <div className="grid sm:grid-cols-2 md:grid-cols-3 gap-6">
          {topics.map(topic => (
            <div
              key={topic.title}
              className="group p-6 sm:p-8 rounded-2xl bg-dark-800 border border-white/5 hover:border-gold-400/30 transition-all duration-300"
            >
              <div className="w-14 h-14 rounded-xl bg-gold-400/10 text-gold-400 flex items-center justify-center mb-5 group-hover:bg-gold-400/20 transition-colors">
                {topic.icon}
              </div>
              <h3 className="text-xl font-display font-semibold text-white mb-3">
                {topic.title}
              </h3>
              <p className="text-silver-400 font-body text-sm leading-relaxed">
                {topic.description}
              </p>
            </div>
          ))}
        </div>
      </div>
    </section>
  )
}
