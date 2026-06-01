import { CommonModule } from '@angular/common';
import { Component } from '@angular/core';
import { ActivatedRoute, RouterLink } from '@angular/router';

@Component({
  selector: 'app-page',
  standalone: true,
  imports: [CommonModule, RouterLink],
  templateUrl: './page.component.html'
})
export class PageComponent {
  title = 'Page';
  description = 'Contenu géré par le router Angular.';
  suffix = '';

  constructor(route: ActivatedRoute) {
    this.title = route.snapshot.data['title'] ?? this.title;
    this.description = route.snapshot.data['description'] ?? this.description;
    this.suffix = route.snapshot.paramMap.get('id') ?? '';
  }
}