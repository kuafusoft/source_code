/*
THIS FILE INCLUDES THE FOLLOWING PERIPHERALS:
ADC
RNG
DMAMUX1

*/
/* 
 1 #ifndef _LOGIC_H                                                            |   " Press <F1> to display hel
  2 #define _LOGIC_H                                                            |
  3                                                                             |-  logic.h (/home/b43646/remot
  4 #define  offset(y,x)        (unsigned int)&(((struct y*)0)->x)              ||-   macro
  5                                                                             |||     _LOGIC_H
  6 typedef struct DATA {                                                       |||     offset
  7     unsigned int value;                                                     ||
  8     unsigned int ex_ret;                                                    ||-   typedef
  9 } *DATA_PTR;                                                                |||     DATA_PTR
 10                                                                             ||
 11 unsigned int Check_Value(DATA_PTR ptr,unsigned int num);                    ||-   struct
 12                                                                             |||     DATA
 13 #endif     
*/ 
#include "../chip/chip.h"
#include "../inc/logic.h"

#define  offset(peri,x)        (unsigned int)&(((struct peri_MemMap*)0)->x)

#define  WIDTH_VALUE_1	0b1
#define  WIDTH_VALUE_2	0b11
#define  WIDTH_VALUE_3	0b111
#define  WIDTH_VALUE_4	0b1111
#define  WIDTH_VALUE_5	0b11111
#define  WIDTH_VALUE_6	0b111111
#define  WIDTH_VALUE_7	0b1111111
#define  WIDTH_VALUE_8	0b11111111
#define  WIDTH_VALUE_9	0b111111111
#define  WIDTH_VALUE_10	0b1111111111
#define  WIDTH_VALUE_11	0b11111111111
#define  WIDTH_VALUE_12	0b111111111111
#define  WIDTH_VALUE_13	0b1111111111111
#define  WIDTH_VALUE_14	0b11111111111111
#define  WIDTH_VALUE_15	0b111111111111111
#define  WIDTH_VALUE_16	0b1111111111111111
#define  WIDTH_VALUE_17	0b11111111111111111
#define  WIDTH_VALUE_18	0b111111111111111111
#define  WIDTH_VALUE_19	0b1111111111111111111
#define  WIDTH_VALUE_20	0b11111111111111111111
#define  WIDTH_VALUE_21	0b111111111111111111111
#define  WIDTH_VALUE_22	0b1111111111111111111111
#define  WIDTH_VALUE_23	0b11111111111111111111111
#define  WIDTH_VALUE_24	0b111111111111111111111111
#define  WIDTH_VALUE_25	0b1111111111111111111111111
#define  WIDTH_VALUE_26	0b11111111111111111111111111
#define  WIDTH_VALUE_27	0b111111111111111111111111111
#define  WIDTH_VALUE_28	0b1111111111111111111111111111
#define  WIDTH_VALUE_29	0b11111111111111111111111111111
#define  WIDTH_VALUE_30	0b111111111111111111111111111111
#define  WIDTH_VALUE_31	0b1111111111111111111111111111111
#define  WIDTH_VALUE_32	0b11111111111111111111111111111111

#define	 MASK(off,bitwidth)	(unsigned int)((WIDTH_VALUE_bitwidth)<<off)
#define  SHIFT(off)	off
#define	 SHIFT_VALUE(off)  (1<<off)

struct DATA ADC_REG_DATA[] = {
        {offset(SC1[0]),0},
        {offset(CFG1),8},
        {offset(CFG2),12},
        {offset(R[0]),16},
        {offset(CV1),24},
        {offset(CV2),28},
        {offset(SC2),32},
        {offset(SC3),36},
        {offset(OFS),40},
        {offset(PG),44},
        {offset(MG),48},
        {offset(CLPD),52},
        {offset(CLPS),56},
        {offset(CLP4),60},
        {offset(CLP3),64},
        {offset(CLP2),68},
        {offset(CLP1),72},
        {offset(CLP0),76},
        {offset(CLMD),84},
        {offset(CLMS),88},
        {offset(CLM4),92},
        {offset(CLM3),96},
        {offset(CLM2),100},
        {offset(CLM1),104},
        {offset(CLM0),108},
        {sizeof(struct ADC_MemMap),112}
};

struct DATA ADC_BITFIELD_DATA[] = {
        {ADC_SC1_ADCH_MASK,MASK(0, 5)}, //offset: 0, bit_width: 5, access: read-write, description:
        {ADC_SC1_ADCH_SHIFT,SHIFT(0)}, //0},
        {ADC_SC1_ADCH(1),SHIFT_VALUE(0)}, //1}
        {ADC_SC1_DIFF_MASK,0x20},
        {ADC_SC1_DIFF_SHIFT,5},
        {ADC_SC1_AIEN_MASK,0x40},
        {ADC_SC1_AIEN_SHIFT,6},
        {ADC_SC1_COCO_MASK,0x80},
        {ADC_SC1_COCO_SHIFT,7},
        {ADC_CFG1_ADICLK_MASK,0x3},
        {ADC_CFG1_ADICLK_SHIFT,0},
        {ADC_CFG1_ADICLK(1),1},
        {ADC_CFG1_MODE_MASK,0xC},
        {ADC_CFG1_MODE_SHIFT,2},
        {ADC_CFG1_MODE(1),4},
        {ADC_CFG1_ADLSMP_MASK,0x10},
        {ADC_CFG1_ADLSMP_SHIFT,4},
        {ADC_CFG1_ADIV_MASK,0x60},
        {ADC_CFG1_ADIV_SHIFT,5},
        {ADC_CFG1_ADIV(1),(1<<5)},
        {ADC_CFG1_ADLPC_MASK,0x80},
        {ADC_CFG1_ADLPC_SHIFT,7},
        {ADC_CFG2_ADLSTS_MASK,0x03},
        {ADC_CFG2_ADLSTS_SHIFT,0},
        {ADC_CFG2_ADLSTS(1),1},
        {ADC_CFG2_ADHSC_MASK,0x04},
        {ADC_CFG2_ADHSC_SHIFT,2},
        {ADC_CFG2_ADACKEN_MASK,0x08},
        {ADC_CFG2_ADACKEN_SHIFT,3},
        {ADC_CFG2_MUXSEL_MASK,0x10},
        {ADC_CFG2_MUXSEL_SHIFT,4},
        {ADC_R_D_MASK,0xFFFF},
        {ADC_R_D_SHIFT,0},
        {ADC_R_D(1),1},
        {ADC_CV1_CV_MASK,0xFFFF},
        {ADC_CV1_CV_SHIFT,0},
        {ADC_CV1_CV(1),1},
        {ADC_CV2_CV_MASK,0xFFFF},
        {ADC_CV2_CV_SHIFT,0},
        {ADC_CV2_CV(1),1},
        {ADC_SC2_REFSEL_MASK,0x03},
        {ADC_SC2_REFSEL_SHIFT,0},
        {ADC_SC2_REFSEL(1),1},
        {ADC_SC2_DMAEN_MASK,0x04},
        {ADC_SC2_DMAEN_SHIFT,0x02},
        {ADC_SC2_ACREN_MASK,0x08},
        {ADC_SC2_ACREN_SHIFT,0x03},
        {ADC_SC2_ACFGT_MASK,0x10},
        {ADC_SC2_ACFGT_SHIFT,0x04},
        {ADC_SC2_ACFE_MASK,0x20},
        {ADC_SC2_ACFE_SHIFT,0x05},
        {ADC_SC2_ADTRG_MASK,0x40},
        {ADC_SC2_ADTRG_SHIFT,0x06},
        {ADC_SC2_ADACT_MASK,0x80},
        {ADC_SC2_ADACT_SHIFT,0x07},
        {ADC_SC3_AVGS_MASK,0x03},
        {ADC_SC3_AVGS_SHIFT,0},
        {ADC_SC3_AVGS(1),1},
        {ADC_SC3_AVGE_MASK,0x04},
        {ADC_SC3_AVGE_SHIFT,0x02},
        {ADC_SC3_ADCO_MASK,0x08},
        {ADC_SC3_ADCO_SHIFT,0x03},
        {ADC_SC3_CALF_MASK,0x40},
        {ADC_SC3_CALF_SHIFT,0x06},
        {ADC_SC3_CAL_MASK,0x80},
        {ADC_SC3_CAL_SHIFT,0x07},
        {ADC_OFS_OFS_MASK,0xFFFF},
        {ADC_OFS_OFS_SHIFT,0},
        {ADC_OFS_OFS(1),1},
        {ADC_PG_PG_MASK,0xFFFF},
        {ADC_PG_PG_SHIFT,0},
        {ADC_PG_PG(1),1},
        {ADC_MG_MG_MASK,0xFFFF},
        {ADC_MG_MG_SHIFT,0},
        {ADC_MG_MG(1),1},
        {ADC_CLPD_CLPD_MASK,0x3F},
        {ADC_CLPD_CLPD_SHIFT,0},
        {ADC_CLPD_CLPD(1),1},
        {ADC_CLPS_CLPS_MASK,0x3F},
        {ADC_CLPS_CLPS_SHIFT,0},
        {ADC_CLPS_CLPS(1),1},
        {ADC_CLP4_CLP4_MASK,0X3FF},
        {ADC_CLP4_CLP4_SHIFT,0},
        {ADC_CLP4_CLP4(1),1},
        {ADC_CLP3_CLP3_MASK,0x1FF},
        {ADC_CLP3_CLP3_SHIFT,0},
        {ADC_CLP3_CLP3(1),1},
        {ADC_CLP2_CLP2_MASK,0xFF},
        {ADC_CLP2_CLP2_SHIFT,0},
        {ADC_CLP2_CLP2(1),1},
        {ADC_CLP1_CLP1_MASK,0x7F},
        {ADC_CLP1_CLP1_SHIFT,0},
        {ADC_CLP1_CLP1(1),1},
        {ADC_CLP0_CLP0_MASK,0x3F},
        {ADC_CLP0_CLP0_SHIFT,0},
        {ADC_CLP0_CLP0(1),1},
        {ADC_CLMD_CLMD_MASK,0x3F},
        {ADC_CLMD_CLMD_SHIFT,0},
        {ADC_CLMD_CLMD(1),1},
        {ADC_CLMS_CLMS_MASK,0x3F},
        {ADC_CLMS_CLMS_SHIFT,0},
        {ADC_CLMS_CLMS(1),1},
        {ADC_CLM4_CLM4_MASK,0x3FF},
        {ADC_CLM4_CLM4_SHIFT,0},
        {ADC_CLM4_CLM4(1),1},
        {ADC_CLM3_CLM3_MASK,0x1FF},
        {ADC_CLM3_CLM3_SHIFT,0},
        {ADC_CLM3_CLM3(1),1},
        {ADC_CLM2_CLM2_MASK,0xFF},
        {ADC_CLM2_CLM2_SHIFT,0},
        {ADC_CLM2_CLM2(1),1},
        {ADC_CLM1_CLM1_MASK,0x7F},
        {ADC_CLM1_CLM1_SHIFT,0},
        {ADC_CLM1_CLM1(1),1},
        {ADC_CLM0_CLM0_MASK,0x3F},
        {ADC_CLM0_CLM0_SHIFT,0},
        {ADC_CLM0_CLM0(1),1}
};
