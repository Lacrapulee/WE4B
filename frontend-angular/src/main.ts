import { bootstrapApplication } from '@angular/platform-browser';
import { registerLocaleData } from '@angular/common';
import localeFr from '@angular/common/locales/fr';
import 'zone.js';
import { appConfig } from './app/app.config';
import { AppComponent } from './app/app';

registerLocaleData(localeFr, 'fr-FR');

bootstrapApplication(AppComponent, appConfig).catch((err) => console.error(err));
