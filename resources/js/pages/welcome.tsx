/* eslint-disable @typescript-eslint/no-unused-vars */
import { Head } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Sparkles, Users, Search, Zap, ArrowRight, TrendingUp, UserCheck, Github, Mail, ExternalLink } from 'lucide-react';
import { login, register } from '@/routes';

interface HomeProps {
    laravelVersion: string;
    phpVersion: string;
}

export default function Welcome({ laravelVersion, phpVersion }: HomeProps) {
    return (
        <>
            <Head title="Welcome to Smart Creative Matching Platform" />

            <div className="min-h-screen bg-white dark:bg-gray-900">
                {/* Navigation Header */}
                <nav className="absolute top-0 left-0 right-0 z-50 px-6 py-4">
                    <div className="max-w-7xl mx-auto flex justify-between items-center">
                        <div className="flex items-center space-x-2">
                            <Sparkles className="h-8 w-8 text-blue-600 dark:text-blue-400" />
                            <span className="text-xl font-bold text-gray-900 dark:text-white">CreativeMatch</span>
                        </div>
                        <div className="flex items-center space-x-4">
                            <Button variant="ghost" asChild>
                                <a href={login().url}>Sign In</a>
                            </Button>
                            <Button asChild>
                                <a href={register().url}>Get Started</a>
                            </Button>
                        </div>
                    </div>
                </nav>

                {/* Hero Section */}
                <section className="relative px-6 pt-40 pb-32">
                    <div className="max-w-4xl mx-auto text-center">
                        <div className="flex justify-center mb-6">
                            <Badge variant="secondary" className="px-3 py-1 text-xs font-medium border border-blue-200 dark:border-blue-800 bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300">
                                <Zap className="w-3 h-3 mr-2" />
                                Powered by AI
                            </Badge>
                        </div>
                        <h1 className="text-5xl md:text-6xl font-bold text-gray-900 dark:text-white mb-6 leading-tight">
                            Smart Creative{' '}
                            <span className="text-blue-600 dark:text-blue-400">
                                Matching
                            </span>
                        </h1>
                        <p className="text-xl md:text-2xl text-gray-600 dark:text-gray-400 mb-12 max-w-3xl mx-auto leading-relaxed">
                            Connect talented creatives with perfect opportunities using AI-powered semantic matching.
                        </p>
                        <div className="flex flex-col sm:flex-row gap-3 justify-center items-center">
                            <Button size="lg" className="text-base px-8 py-4 h-auto font-semibold" asChild>
                                <a href={register().url}>
                                    <Users className="w-4 h-4 mr-2" />
                                    Join as Creative
                                </a>
                            </Button>
                            <Button size="lg" variant="outline" className="text-base px-8 py-4 h-auto font-semibold border-gray-300 dark:border-gray-600" asChild>
                                <a href={register().url}>
                                    <Search className="w-4 h-4 mr-2" />
                                    Post Opportunities
                                </a>
                            </Button>
                        </div>
                    </div>
                </section>

                {/* Key Features */}
                <section className="px-6 py-24 bg-gray-50 dark:bg-gray-800/30 border-t border-gray-200 dark:border-gray-700">
                    <div className="max-w-6xl mx-auto">
                        <div className="text-center mb-16">
                            <h2 className="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                                Key Features
                            </h2>
                            <p className="text-lg text-gray-600 dark:text-gray-400 max-w-2xl mx-auto">
                                Discover how our platform revolutionizes creative matching through advanced technology
                            </p>
                        </div>
                        <div className="grid md:grid-cols-3 gap-12 text-center">
                            <div className="group p-6 rounded-lg border border-gray-200 dark:border-gray-700 hover:border-blue-300 dark:hover:border-blue-600 transition-all duration-300 hover:shadow-lg">
                                <div className="w-16 h-16 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-6 group-hover:bg-blue-100 dark:group-hover:bg-blue-900/30 transition-colors duration-300">
                                    <Sparkles className="w-8 h-8 text-gray-500 dark:text-gray-400 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors duration-300" />
                                </div>
                                <h3 className="text-xl font-bold text-gray-900 dark:text-white mb-4">AI-Powered Matching</h3>
                                <p className="text-gray-600 dark:text-gray-400 leading-relaxed">
                                    Advanced semantic search to understand context beyond keywords
                                </p>
                            </div>
                            <div className="group p-6 rounded-lg border border-gray-200 dark:border-gray-700 hover:border-blue-300 dark:hover:border-blue-600 transition-all duration-300 hover:shadow-lg">
                                <div className="w-16 h-16 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-6 group-hover:bg-blue-100 dark:group-hover:bg-blue-900/30 transition-colors duration-300">
                                    <TrendingUp className="w-8 h-8 text-gray-500 dark:text-gray-400 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors duration-300" />
                                </div>
                                <h3 className="text-xl font-bold text-gray-900 dark:text-white mb-4">Smart Recommendations</h3>
                                <p className="text-gray-600 dark:text-gray-400 leading-relaxed">
                                    Personalized suggestions based on compatibility scores and success patterns
                                </p>
                            </div>
                            <div className="group p-6 rounded-lg border border-gray-200 dark:border-gray-700 hover:border-blue-300 dark:hover:border-blue-600 transition-all duration-300 hover:shadow-lg">
                                <div className="w-16 h-16 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-6 group-hover:bg-blue-100 dark:group-hover:bg-blue-900/30 transition-colors duration-300">
                                    <UserCheck className="w-8 h-8 text-gray-500 dark:text-gray-400 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors duration-300" />
                                </div>
                                <h3 className="text-xl font-bold text-gray-900 dark:text-white mb-4">Quality Connections</h3>
                                <p className="text-gray-600 dark:text-gray-400 leading-relaxed">
                                    Verified professionals ensuring high-quality matches for long-term success
                                </p>
                            </div>
                        </div>
                    </div>
                </section>

                {/* CTA Section */}
                <section className="px-6 py-24 bg-white dark:bg-gray-900">
                    <div className="max-w-4xl mx-auto text-center">
                        <h2 className="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                            Ready to Get Started?
                        </h2>
                        <p className="text-lg text-gray-600 dark:text-gray-400 mb-8 max-w-2xl mx-auto">
                            Join the AI-powered creative matching platform and discover your next opportunity.
                        </p>
                        <Button size="lg" className="text-base px-8 py-4 h-auto font-semibold" asChild>
                            <a href={register().url}>
                                Get Started Free
                                <ArrowRight className="w-4 h-4 ml-2" />
                            </a>
                        </Button>
                    </div>
                </section>

                {/* Footer */}
                <footer className="bg-gray-50 dark:bg-gray-900 border-t border-gray-200 dark:border-gray-800">
                    <div className="max-w-7xl mx-auto px-6 py-12">
                        <div className="grid grid-cols-1 md:grid-cols-5 gap-8">
                            {/* Brand Section */}
                            <div className="col-span-1 md:col-span-2">
                                <div className="flex items-center space-x-2 mb-4">
                                    <Sparkles className="h-6 w-6 text-blue-600 dark:text-blue-400" />
                                    <span className="text-lg font-bold text-gray-900 dark:text-white">CreativeMatch</span>
                                </div>
                                <p className="text-gray-600 dark:text-gray-400 mb-4 max-w-md">
                                    An open-source AI-powered platform connecting talented creatives with perfect opportunities through semantic matching.
                                </p>
                                <div className="flex space-x-4">
                                    <a
                                        href="https://github.com/adhika16/smart-matching-platform"
                                        className="text-gray-500 hover:text-gray-900 dark:hover:text-white transition-colors"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                    >
                                        <Github className="h-5 w-5" />
                                    </a>
                                </div>
                            </div>

                            {/* Product Links */}
                            <div>
                                <h3 className="text-sm font-semibold text-gray-900 dark:text-white uppercase tracking-wider mb-4">
                                    Product
                                </h3>
                                <ul className="space-y-2">
                                    <li>
                                        <a href="#features" className="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">
                                            Features
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#" className="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">
                                            Documentation
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#" className="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">
                                            API Reference
                                        </a>
                                    </li>
                                </ul>
                            </div>

                            <div>
                                <h3 className="text-sm font-semibold text-gray-900 dark:text-white uppercase tracking-wider mb-4">
                                    Help
                                </h3>
                                <ul className="space-y-2">
                                    <li>
                                        <a href="#" className="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">
                                            Contact
                                        </a>
                                    </li>
                                </ul>
                            </div>

                            {/* Community Links */}
                            <div>
                                <h3 className="text-sm font-semibold text-gray-900 dark:text-white uppercase tracking-wider mb-4">
                                    Community
                                </h3>
                                <ul className="space-y-2">
                                    <li>
                                        <a
                                            href="https://github.com/adhika16/smart-matching-platform"
                                            className="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors inline-flex items-center"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                        >
                                            GitHub
                                            <ExternalLink className="h-3 w-3 ml-1" />
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#" className="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">
                                            Contribute
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#" className="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">
                                            Issues
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        {/* Bottom Section */}
                        <div className="mt-8 pt-8 border-t border-gray-200 dark:border-gray-800">
                            <div className="flex flex-col md:flex-row justify-between items-center">
                                <div className="text-sm text-gray-500 dark:text-gray-400">
                                    <p>© {new Date().getFullYear()} CreativeMatch. Open source project built with Laravel.</p>
                                </div>
                                <div className="mt-4 md:mt-0 flex space-x-6">
                                    <a href="#" className="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">
                                        Privacy Policy
                                    </a>
                                    <a href="#" className="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">
                                        Terms of Service
                                    </a>
                                    <a
                                        href="https://github.com/adhika16/smart-matching-platform/blob/main/LICENSE"
                                        className="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                    >
                                        MIT License
                                    </a>
                                </div>
                            </div>
                        </div>

                        {/* Decorative SVG Text */}
                        <div className="w-full font-extrabold -mb-12">
                            <svg viewBox="0 0 100 20" className="w-full h-auto block" role="img" aria-label="Headline">
                                <text
                                    x="50%" y="50%"
                                    textAnchor="middle"
                                    dominantBaseline="middle"
                                    fontSize="10"
                                    className="fill-blue-600 dark:fill-blue-400">
                                    CreativeMatch
                                </text>
                            </svg>
                        </div>
                    </div>
                </footer>
            </div>
        </>
    );
}
