import { Component, EventEmitter, Input, Output } from '@angular/core';
import { CommonModule } from '@angular/common';

@Component({
  selector: 'app-favoris-button',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './favoris-button.html',
  styleUrls: ['./favoris-button.css']
})
export class FavorisButtonComponent {
  @Input() isFavoris: boolean = false;
  @Input() disabled: boolean = false;
  @Output() toggle = new EventEmitter<void>();

  onClick(ev: Event) {
    ev.stopPropagation();
    if (this.disabled) return;
    this.toggle.emit();
  }
}
