; ---------------------------------------------------------------------------------------
;  @file:    startup_MKM34Z5.s
;  @purpose: CMSIS Cortex-M0P Core Device Startup File
;            MKM34Z5
;  @version: 1.6
;  @date:    2014-6-25
;  @build:   b140626
; ---------------------------------------------------------------------------------------
;
; Copyright (c) 1997 - 2014 , Freescale Semiconductor, Inc.
; All rights reserved.
;
; Redistribution and use in source and binary forms, with or without modification,
; are permitted provided that the following conditions are met:
;
; o Redistributions of source code must retain the above copyright notice, this list
;   of conditions and the following disclaimer.
;
; o Redistributions in binary form must reproduce the above copyright notice, this
;   list of conditions and the following disclaimer in the documentation and/or
;   other materials provided with the distribution.
;
; o Neither the name of Freescale Semiconductor, Inc. nor the names of its
;   contributors may be used to endorse or promote products derived from this
;   software without specific prior written permission.
;
; THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
; ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
; WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
; DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR
; ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
; (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON
; ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
; (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
; SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
;
; The modules in this file are included in the libraries, and may be replaced
; by any user-defined modules that define the PUBLIC symbol _program_start or
; a user defined start symbol.
; To override the cstartup defined in the library, simply add your modified
; version to the workbench project.
;
; The vector table is normally located at address 0.
; When debugging in RAM, it can be located in RAM, aligned to at least 2^6.
; The name "__vector_table" has special meaning for C-SPY:
; it is where the SP start value is found, and the NVIC vector
; table register (VTOR) is initialized to this address if != 0.
;
; Cortex-M version
;

        MODULE  ?cstartup

        ;; Forward declaration of sections.
        SECTION CSTACK:DATA:NOROOT(3)

        SECTION .intvec:CODE:NOROOT(2)

        EXTERN  __iar_program_start
        EXTERN  SystemInit
        PUBLIC  __vector_table
        PUBLIC  __vector_table_0x1c
        PUBLIC  __Vectors
        PUBLIC  __Vectors_End
        PUBLIC  __Vectors_Size

        DATA

__vector_table
        DCD     sfe(CSTACK)
        DCD     Reset_Handler

        DCD     NMI_Handler                                   ;NMI Handler
        DCD     HardFault_Handler                             ;Hard Fault Handler
        DCD     0                                             ;Reserved
        DCD     0                                             ;Reserved
        DCD     0                                             ;Reserved
__vector_table_0x1c
        DCD     0                                             ;Reserved
        DCD     0                                             ;Reserved
        DCD     0                                             ;Reserved
        DCD     0                                             ;Reserved
        DCD     SVC_Handler                                   ;SVCall Handler
        DCD     0                                             ;Reserved
        DCD     0                                             ;Reserved
        DCD     PendSV_Handler                                ;PendSV Handler
        DCD     SysTick_Handler                               ;SysTick Handler

                                                              ;External Interrupts
        DCD     DMA0_IRQHandler                               ;DMA channel 0 transfer complete
        DCD     DMA1_IRQHandler                               ;DMA channel 1 transfer complete
        DCD     DMA2_IRQHandler                               ;DMA channel 2 transfer complete
        DCD     DMA3_IRQHandler                               ;DMA channel 3 transfer complete
        DCD     SPI0_IRQHandler                               ;SPI0 ORed interrupt
        DCD     SPI1_IRQHandler                               ;SPI1 ORed interrupt
        DCD     PMC_IRQHandler                                ;Low-voltage detect, low-voltage warning
        DCD     TMR0_IRQHandler                               ;Quad Timer Channel 0
        DCD     TMR1_IRQHandler                               ;Quad Timer Channel 1
        DCD     TMR2_IRQHandler                               ;Quad Timer Channel 2
        DCD     TMR3_IRQHandler                               ;Quad Timer Channel 3
        DCD     PIT0_PIT1_IRQHandler                          ;PIT0/PIT1 ORed interrupt
        DCD     LLWU_IRQHandler                               ;Low Leakage Wakeup
        DCD     FTFA_IRQHandler                               ;Command complete and read collision
        DCD     CMP0_CMP1_IRQHandler                          ;CMP0/CMP1 ORed interrupt
        DCD     LCD_IRQHandler                                ;LCD interrupt
        DCD     ADC_IRQHandler                                ;ADC interrupt
        DCD     PTx_IRQHandler                                ;Single interrupt vector for GPIOA,GPIOB,GPIOC,GPIOD,GPIOE,GPIOF,GPIOG,GPIOH,GPIOI
        DCD     RNGA_IRQHandler                               ;RNGA interrupt
        DCD     UART0_UART1_IRQHandler                        ;UART0/UART1 ORed interrupt
        DCD     UART2_UART3_IRQHandler                        ;UART2/UART3 ORed interrupt
        DCD     AFE_CH0_IRQHandler                            ;AFE Channel 0
        DCD     AFE_CH1_IRQHandler                            ;AFE Channel 1
        DCD     AFE_CH2_IRQHandler                            ;AFE Channel 2
        DCD     AFE_CH3_IRQHandler                            ;AFE Channel 3
        DCD     RTC_IRQHandler                                ;IRTC interrupt
        DCD     I2C0_I2C1_IRQHandler                          ;I2C0/I2C1 ORed interrupt
        DCD     EWM_IRQHandler                                ;EWM interrupt
        DCD     MCG_IRQHandler                                ;MCG interrupt
        DCD     Watchdog_IRQHandler                           ;WDOG interrupt
        DCD     LPTMR_IRQHandler                              ;LPTMR interrupt
        DCD     XBAR_IRQHandler                               ;XBAR interrupt
__Vectors_End

        SECTION FlashConfig:CODE
__FlashConfig
      	DCD	0xFFFFFFFF
      	DCD	0xFFFFFFFF
      	DCD	0xFFFFFFFF
      	DCD	0xFFFFFFFE
__FlashConfig_End

__Vectors       EQU   __vector_table
__Vectors_Size 	EQU   __Vectors_End - __Vectors

_NVIC_ICER      EQU   0xE000E180
_NVIC_ICPR      EQU   0xE000E280

;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;
;;
;; Default interrupt handlers.
;;
        THUMB

        PUBWEAK Reset_Handler
        SECTION .text:CODE:REORDER:NOROOT(2)
Reset_Handler
        CPSID   I               ; Mask interrupts
        LDR R0, =_NVIC_ICER    ; Disable interrupts and clear pending flags
        LDR R1, =_NVIC_ICPR
        LDR R2, =0xFFFFFFFF
        STR R2, [R0]        ; NVIC_ICER - clear enable IRQ register
        STR R2, [R1]        ; NVIC_ICPR - clear pending IRQ register
        LDR     R0, =SystemInit
        BLX     R0
        CPSIE   I               ; Unmask interrupts
        LDR     R0, =__iar_program_start
        BX      R0

        PUBWEAK NMI_Handler
        SECTION .text:CODE:REORDER:NOROOT(1)
NMI_Handler
        B .

        PUBWEAK HardFault_Handler
        SECTION .text:CODE:REORDER:NOROOT(1)
HardFault_Handler
        B .

        PUBWEAK SVC_Handler
        SECTION .text:CODE:REORDER:NOROOT(1)
SVC_Handler
        B .

        PUBWEAK PendSV_Handler
        SECTION .text:CODE:REORDER:NOROOT(1)
PendSV_Handler
        B .

        PUBWEAK SysTick_Handler
        SECTION .text:CODE:REORDER:NOROOT(1)
SysTick_Handler
        B .

        PUBWEAK DMA0_IRQHandler
        PUBWEAK DMA1_IRQHandler
        PUBWEAK DMA2_IRQHandler
        PUBWEAK DMA3_IRQHandler
        PUBWEAK SPI0_IRQHandler
        PUBWEAK SPI1_IRQHandler
        PUBWEAK PMC_IRQHandler
        PUBWEAK TMR0_IRQHandler
        PUBWEAK TMR1_IRQHandler
        PUBWEAK TMR2_IRQHandler
        PUBWEAK TMR3_IRQHandler
        PUBWEAK PIT0_PIT1_IRQHandler
        PUBWEAK LLWU_IRQHandler
        PUBWEAK FTFA_IRQHandler
        PUBWEAK CMP0_CMP1_IRQHandler
        PUBWEAK LCD_IRQHandler
        PUBWEAK ADC_IRQHandler
        PUBWEAK PTx_IRQHandler
        PUBWEAK RNGA_IRQHandler
        PUBWEAK UART0_UART1_IRQHandler
        PUBWEAK UART2_UART3_IRQHandler
        PUBWEAK AFE_CH0_IRQHandler
        PUBWEAK AFE_CH1_IRQHandler
        PUBWEAK AFE_CH2_IRQHandler
        PUBWEAK AFE_CH3_IRQHandler
        PUBWEAK RTC_IRQHandler
        PUBWEAK I2C0_I2C1_IRQHandler
        PUBWEAK EWM_IRQHandler
        PUBWEAK MCG_IRQHandler
        PUBWEAK Watchdog_IRQHandler
        PUBWEAK LPTMR_IRQHandler
        PUBWEAK XBAR_IRQHandler
        PUBWEAK DefaultISR
        SECTION .text:CODE:REORDER:NOROOT(1)
DMA0_IRQHandler
DMA1_IRQHandler
DMA2_IRQHandler
DMA3_IRQHandler
SPI0_IRQHandler
SPI1_IRQHandler
PMC_IRQHandler
TMR0_IRQHandler
TMR1_IRQHandler
TMR2_IRQHandler
TMR3_IRQHandler
PIT0_PIT1_IRQHandler
LLWU_IRQHandler
FTFA_IRQHandler
CMP0_CMP1_IRQHandler
LCD_IRQHandler
ADC_IRQHandler
PTx_IRQHandler
RNGA_IRQHandler
UART0_UART1_IRQHandler
UART2_UART3_IRQHandler
AFE_CH0_IRQHandler
AFE_CH1_IRQHandler
AFE_CH2_IRQHandler
AFE_CH3_IRQHandler
RTC_IRQHandler
I2C0_I2C1_IRQHandler
EWM_IRQHandler
MCG_IRQHandler
Watchdog_IRQHandler
LPTMR_IRQHandler
XBAR_IRQHandler
DefaultISR
        B .

        END
