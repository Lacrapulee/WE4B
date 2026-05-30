import { Component, Input, Output, EventEmitter } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';
import { FavorisButtonComponent } from '../../shared/favoris-button';

@Component({
  selector: 'app-article',
  standalone: true,
  imports: [CommonModule, RouterModule, FavorisButtonComponent],
  templateUrl: './article.html',
  styleUrls: ['./article.css']
})
export class ArticleComponent {
  @Input() item: any;
  @Input() isLoggedIn: boolean = false;
  @Input() userId: number | null = null;
  @Output() toggleFavoris = new EventEmitter<any>();

  onToggleFavoris(ev?: Event) {
    this.toggleFavoris.emit(this.item);
  }
}
