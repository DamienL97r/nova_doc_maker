import { startStimulusApp } from '@symfony/stimulus-bridge';
import QuoteAiController from './controllers/quote_ai_controller.js';
export const app = startStimulusApp(require.context('@symfony/stimulus-bridge/lazy-controller-loader!./controllers', true, /\.js$/));
app.register('quote-ai', QuoteAiController);