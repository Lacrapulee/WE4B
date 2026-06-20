import { Component, Input, Output, EventEmitter } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';

@Component({
  selector: 'app-chat-window',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './chat-window.component.html',
  styleUrls: ['./chat-window.component.css']
})
export class ChatWindowComponent {
  @Input() selectedConversation: any = null;
  @Input() activeChatMessages: any[] = [];
  @Output() back = new EventEmitter<void>();
  @Output() send = new EventEmitter<string>();

  newMessage = '';

  onBack() {
    this.back.emit();
  }

  onSend() {
    if (!this.newMessage.trim()) return;
    this.send.emit(this.newMessage);
    this.newMessage = '';
  }
}
